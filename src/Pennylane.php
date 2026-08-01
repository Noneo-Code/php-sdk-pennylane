<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;

final class Pennylane extends Connector
{
    use HandlesPennylaneErrors;
    use RespectsPennylaneRateLimits;

    public function __construct(Authenticator $authenticator, ?RateLimitStore $rateLimitStore = null)
    {
        $this->authenticate($authenticator);

        if ($rateLimitStore instanceof RateLimitStore) {
            $this->useRateLimitStore($rateLimitStore);
        }
    }

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/v2';
    }
}
