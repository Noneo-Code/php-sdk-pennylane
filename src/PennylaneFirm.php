<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Http\Connector\FirmConnector;
use Noneo\Sdk\Pennylane\Resource\Firm\Companies;
use Psr\SimpleCache\CacheInterface;

/**
 * Client de la Firm API v1. Façade sans héritage Saloon : le transport est un
 * détail d'implémentation, remplaçable sans toucher au contrat public.
 */
final class PennylaneFirm
{
    /**
     * @see Pennylane::VERSION — même paquet, même version.
     */
    public const string VERSION = Pennylane::VERSION;

    public const int DEFAULT_MAX_RETRIES = FirmConnector::DEFAULT_MAX_RETRIES;

    private readonly FirmConnector $connector;

    /**
     * `$apiToken` est marqué sensible : une trace conservant les arguments
     * afficherait le jeton en clair.
     */
    public function __construct(
        #[\SensitiveParameter] string $apiToken,
        ?CacheInterface $rateLimitCache = null,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        ?float $requestTimeout = null,
        ?float $connectTimeout = null,
    ) {
        $this->connector = new FirmConnector(
            $apiToken,
            $rateLimitCache,
            $maxRetries,
            $requestTimeout,
            $connectTimeout,
        );
    }

    /**
     * Accès au transport Saloon sous-jacent, hors contrat de compatibilité.
     *
     * @internal réservé à `Testing\` et aux tests du SDK
     */
    public function connector(): FirmConnector
    {
        return $this->connector;
    }

    public function company(int $companyId): CompanyScope
    {
        return new CompanyScope($this->connector, $companyId);
    }

    public function companies(): Companies
    {
        return new Companies($this->connector);
    }
}
