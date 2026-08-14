<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http\Connector;

use Noneo\Sdk\Pennylane\Concerns\AppliesPennylaneTimeouts;
use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RejectsFloatValues;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Concerns\SecuresPennylaneTransport;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;

/**
 * Transport de la Firm API restreint à une entreprise : préfixe
 * `/companies/{id}` à toutes les requêtes. C'est un `Connector` à part entière
 * (et non un simple relais) car `BaseResource` et `CursorPaginator` exigent
 * tous deux un `Saloon\Http\Connector` concret.
 *
 * @internal
 */
final class CompanyScopeConnector extends Connector
{
    use AppliesPennylaneTimeouts;
    use HandlesPennylaneErrors;
    use RejectsFloatValues;
    use RespectsPennylaneRateLimits;
    use SecuresPennylaneTransport;

    public function __construct(
        private readonly FirmConnector $firm,
        private readonly int $companyId,
    ) {
        $authenticator = $this->firm->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
            $this->authenticate($authenticator);
        }

        $this->useRateLimitStore($this->firm->getRateLimitStore());
        $this->useMaxRetries($this->firm->getMaxRetries());
        $this->useTimeouts($this->firm->getRequestTimeout(), $this->firm->getConnectTimeout());

        $mockClient = $this->firm->getMockClient();

        if ($mockClient instanceof MockClient) {
            $this->withMockClient($mockClient);
        }
    }

    public function resolveBaseUrl(): string
    {
        return rtrim($this->firm->resolveBaseUrl(), '/').'/companies/'.$this->companyId;
    }
}
