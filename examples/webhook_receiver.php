<?php

/*
 * Récepteur de webhooks Pennylane, autonome et sans framework.
 *
 * À servir derrière n'importe quel serveur HTTP, par exemple pour un essai
 * local :
 *
 *     PENNYLANE_WEBHOOK_SECRET=whsec_… php -S 127.0.0.1:8000 examples/webhook_receiver.php
 *
 * Le secret n'est connu qu'à la création de l'abonnement : il n'est renvoyé
 * qu'une seule fois, par `webhookSubscriptions()->create()`, dans le champ
 * `secret` du DTO. Stockez-le à ce moment-là — un `GET` ultérieur ne le rend
 * plus.
 *
 *     $subscription = $pennylane->webhookSubscriptions()->create(
 *         callbackUrl: 'https://exemple.test/webhooks/pennylane',
 *         events: [WebhookEventType::DmsFileCreated],
 *     );
 *     // $subscription->secret → à ranger dans votre coffre à secrets.
 */

declare(strict_types=1);

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Exception\InvalidWebhookSignatureException;
use Noneo\Sdk\Pennylane\Webhook\SignatureVerifier;

require __DIR__.'/../vendor/autoload.php';

$secret = getenv('PENNYLANE_WEBHOOK_SECRET');

if (!is_string($secret) || '' === $secret) {
    fwrite(STDERR, "Renseignez le secret d'abonnement dans PENNYLANE_WEBHOOK_SECRET.\n");
    exit(1);
}

// Le corps *brut* : re-sérialiser un tableau décodé changerait un espace ou
// l'ordre des clés, et l'empreinte ne correspondrait plus.
$rawBody = (string) file_get_contents('php://input');

// `SignatureVerifier::HEADER` porte le nom de l'en-tête ; PHP le présente dans
// $_SERVER sous sa forme CGI (majuscules, tirets en soulignés, préfixe HTTP_).
$signature = $_SERVER['HTTP_'.strtoupper(str_replace('-', '_', SignatureVerifier::HEADER))] ?? '';

// Fenêtre anti-rejeu de 300 s par défaut : une requête capturée puis rejouée
// plus tard est refusée, même avec une signature valide.
$verifier = new SignatureVerifier($secret);

try {
    // Vérifie *puis* décode : impossible d'oublier la première moitié.
    $event = $verifier->constructEvent($rawBody, is_string($signature) ? $signature : '');
} catch (InvalidWebhookSignatureException $e) {
    http_response_code(401);
    echo $e->getMessage()."\n";

    exit;
} catch (JsonException|InvalidArgumentException $e) {
    http_response_code(400);
    echo $e->getMessage()."\n";

    exit;
}

// Répondez vite : Pennylane rejoue l'événement si vous tardez ou échouez,
// et `retryCount` dit combien de fois il l'a déjà tenté. Le traitement lourd
// a sa place dans une file, pas ici.
http_response_code(200);

error_log(sprintf(
    'Événement Pennylane %s #%d (tentative %d) pour l\'entreprise %s',
    $event->eventName,
    $event->id,
    $event->retryCount,
    $event->companyId,
));

// `$event->event` vaut `null` si Pennylane a publié un type postérieur à cette
// version du SDK ; `$event->eventName` reste lisible dans ce cas.
switch ($event->event) {
    case WebhookEventType::CustomerInvoiceEInvoicingStatusUpdated:
        // `previousAttributes` porte l'ancien état des champs modifiés : de quoi
        // distinguer une vraie transition d'un simple événement redondant.
        error_log(sprintf(
            'Statut e-invoicing : %s → %s',
            json_encode($event->previousAttributes['e_invoicing_status'] ?? null, JSON_THROW_ON_ERROR),
            json_encode($event->object['e_invoicing_status'] ?? null, JSON_THROW_ON_ERROR),
        ));
        break;

    case WebhookEventType::DmsFileCreated:
        error_log(sprintf('Nouveau fichier DMS : %s', json_encode($event->object['name'] ?? null, JSON_THROW_ON_ERROR)));
        break;
}

echo "OK\n";
