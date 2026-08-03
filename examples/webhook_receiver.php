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

use Noneo\Sdk\Pennylane\Webhook\SignatureVerifier;
use Noneo\Sdk\Pennylane\Webhook\WebhookEvent;

require __DIR__.'/../vendor/autoload.php';

$secret = getenv('PENNYLANE_WEBHOOK_SECRET');

if (!is_string($secret) || '' === $secret) {
    fwrite(STDERR, "Renseignez le secret d'abonnement dans PENNYLANE_WEBHOOK_SECRET.\n");
    exit(1);
}

// Le corps *brut* : re-sérialiser un tableau décodé changerait un espace ou
// l'ordre des clés, et l'empreinte ne correspondrait plus.
$rawBody = (string) file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PENNYLANE_SIGNATURE'] ?? '';

// Fenêtre anti-rejeu de 300 s par défaut : une requête capturée puis rejouée
// plus tard est refusée, même avec une signature valide.
$verifier = new SignatureVerifier($secret);

if (!is_string($signature) || !$verifier->verify($rawBody, $signature)) {
    http_response_code(401);
    echo "Signature invalide.\n";

    exit;
}

$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo "Corps illisible.\n";

    exit;
}

try {
    $event = WebhookEvent::fromArray($payload);
} catch (InvalidArgumentException $e) {
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
    $event->event,
    $event->id,
    $event->retryCount,
    $event->companyId,
));

switch ($event->event) {
    case 'customer_invoice.e_invoicing_status_updated':
        // `previousAttributes` porte l'ancien état des champs modifiés : de quoi
        // distinguer une vraie transition d'un simple événement redondant.
        error_log(sprintf(
            'Statut e-invoicing : %s → %s',
            json_encode($event->previousAttributes['e_invoicing_status'] ?? null, JSON_THROW_ON_ERROR),
            json_encode($event->object['e_invoicing_status'] ?? null, JSON_THROW_ON_ERROR),
        ));
        break;

    case 'dms_file.created':
        error_log(sprintf('Nouveau fichier DMS : %s', json_encode($event->object['name'] ?? null, JSON_THROW_ON_ERROR)));
        break;
}

echo "OK\n";
