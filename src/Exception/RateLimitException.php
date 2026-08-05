<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

use Saloon\RateLimitPlugin\Helpers\RetryAfterHelper;

final class RateLimitException extends PennylaneException
{
    public function retryAfter(): ?int
    {
        $header = $this->responseHeaders()['Retry-After'] ?? null;

        return RetryAfterHelper::parse($header);
    }
}
