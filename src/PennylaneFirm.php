<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Concerns\AppliesPennylaneTimeouts;
use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Resource\Firm\Companies;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;

final class PennylaneFirm extends Connector
{
    use AppliesPennylaneTimeouts;
    use HandlesPennylaneErrors;
    use RespectsPennylaneRateLimits;

    public function __construct(
        Authenticator $authenticator,
        ?RateLimitStore $rateLimitStore = null,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        ?float $requestTimeout = null,
        ?float $connectTimeout = null,
    ) {
        $this->authenticate($authenticator);

        if ($rateLimitStore instanceof RateLimitStore) {
            $this->useRateLimitStore($rateLimitStore);
        }

        $this->useMaxRetries($maxRetries);
        $this->useTimeouts($requestTimeout, $connectTimeout);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/firm/v1';
    }

    public function company(int $companyId): CompanyScope
    {
        return new CompanyScope($this, $companyId);
    }

    public function companies(): Companies
    {
        return new Companies($this);
    }
}
