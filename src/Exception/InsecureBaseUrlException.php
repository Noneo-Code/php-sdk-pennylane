<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

/**
 * Levée **avant** l'envoi : l'appel n'a pas quitté le process, donc le jeton
 * n'a pas été exposé en clair sur le réseau.
 */
final class InsecureBaseUrlException extends \InvalidArgumentException
{
    public function __construct(private readonly string $url)
    {
        parent::__construct(sprintf(
            'Refus d\'émettre une requête authentifiée hors HTTPS : %s. '
            .'Le jeton voyagerait en clair. Seuls les hôtes de bouclage '
            .'(localhost, 127.0.0.1, [::1]) font exception.',
            $url,
        ));
    }

    public function url(): string
    {
        return $this->url;
    }
}
