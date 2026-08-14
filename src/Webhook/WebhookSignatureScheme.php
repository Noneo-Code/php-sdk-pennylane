<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Webhook;

/**
 * Les deux hypothèses en présence sur le format de signature des webhooks
 * Pennylane. Le schéma se choisit **à la construction** du vérificateur, jamais
 * d'après le contenu de l'en-tête reçu : une auto-détection laisserait un
 * attaquant réclamer le format le plus faible.
 */
enum WebhookSignatureScheme
{
    /**
     * En-tête `t=<unix>,v1=<hex>`, HMAC-SHA256 sur `"{timestamp}.{corps}"`.
     * Porte sa propre fenêtre anti-rejeu.
     */
    case Timestamped;

    /**
     * HMAC-SHA256 sur le corps brut seul, rendu en hexadécimal (casse
     * indifférente) ou en base64, éventuellement préfixé de `sha256=` ou
     * `hmac-sha256=`. Sans horodatage, donc **sans anti-rejeu protocolaire**.
     */
    case BodyOnly;
}
