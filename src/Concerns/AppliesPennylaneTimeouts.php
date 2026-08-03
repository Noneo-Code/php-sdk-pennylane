<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use Saloon\Traits\Plugins\HasTimeout;

/**
 * Sans ces bornes, un connecteur hérite des défauts de Guzzle — soit aucune
 * limite de lecture : un endpoint Pennylane lent, au premier rang desquels les
 * exports comptables, bloque le process indéfiniment. Les valeurs par défaut
 * s'alignent sur ce que pratiquent les autres clients de l'API (60 s au total,
 * 10 s pour établir la connexion).
 *
 * `HasTimeout` lit les propriétés `$connectTimeout` / `$requestTimeout` via
 * `property_exists()` ; les connecteurs étant `final`, la surcharge passe par
 * `useTimeouts()` plutôt que par une redéfinition de propriété.
 */
trait AppliesPennylaneTimeouts
{
    use HasTimeout;

    /**
     * Délai maximal pour établir la connexion TCP/TLS, en secondes.
     */
    public const float DEFAULT_CONNECT_TIMEOUT = 10.0;

    /**
     * Délai maximal pour l'échange complet, en secondes.
     */
    public const float DEFAULT_REQUEST_TIMEOUT = 60.0;

    protected float $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;

    protected float $requestTimeout = self::DEFAULT_REQUEST_TIMEOUT;

    /**
     * Un argument `null` laisse le défaut en place ; `0` vaut « pas de limite »
     * au sens de Guzzle, et une valeur négative — qui n'a pas de sens — y est
     * ramenée.
     */
    public function useTimeouts(?float $requestTimeout = null, ?float $connectTimeout = null): static
    {
        if (null !== $requestTimeout) {
            $this->requestTimeout = max(0.0, $requestTimeout);
        }

        if (null !== $connectTimeout) {
            $this->connectTimeout = max(0.0, $connectTimeout);
        }

        return $this;
    }
}
