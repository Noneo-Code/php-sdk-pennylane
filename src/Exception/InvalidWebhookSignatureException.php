<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

/**
 * Levée à la **réception** d'un webhook, hors de toute réponse HTTP : elle ne
 * rejoint donc pas l'arbre `PennylaneException`, réservé aux erreurs rendues
 * par l'API — même choix que `InsecureBaseUrlException`.
 *
 * Le message ne reprend ni le corps reçu ni la signature attendue : le premier
 * porte des données comptables et vient d'un émetteur non authentifié, la
 * seconde donnerait un oracle à qui lit les journaux.
 */
final class InvalidWebhookSignatureException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Signature de webhook Pennylane invalide : en-tête absent, mal formé, '
            .'calculé avec un autre secret, ou horodatage hors de la fenêtre de tolérance.'
        );
    }
}
