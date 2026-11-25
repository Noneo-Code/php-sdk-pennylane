<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http\Connector;

use Noneo\Sdk\Pennylane\Concerns\AppliesPennylaneTimeouts;
use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RejectsFloatValues;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Concerns\SecuresPennylaneTransport;
use Psr\SimpleCache\CacheInterface;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

/**
 * Transport Saloon de la Firm API. Détail d'implémentation : rien ici
 * n'appartient au contrat public du SDK, qui s'arrête à la façade
 * {@see \Noneo\Sdk\Pennylane\PennylaneFirm}.
 *
 * @internal
 */
final class FirmConnector extends Connector
{
    use AppliesPennylaneTimeouts;
    use HandlesPennylaneErrors;
    use RejectsFloatValues;
    use RespectsPennylaneRateLimits;
    use SecuresPennylaneTransport;

    public function __construct(
        #[\SensitiveParameter] string $apiToken,
        ?CacheInterface $rateLimitCache = null,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        ?float $requestTimeout = null,
        ?float $connectTimeout = null,
    ) {
        $this->authenticate(new TokenAuthenticator($apiToken));

        if ($rateLimitCache instanceof CacheInterface) {
            $this->useRateLimitStore(new Psr16RateLimitStore($rateLimitCache));
        }

        $this->useMaxRetries($maxRetries);
        $this->useTimeouts($requestTimeout, $connectTimeout);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/firm/v1';
    }
}
