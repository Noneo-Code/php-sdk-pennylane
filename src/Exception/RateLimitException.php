<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

use Saloon\RateLimitPlugin\Helpers\RetryAfterHelper;

final class RateLimitException extends PennylaneException
{
    public function retryAfter(): ?int
    {
        $header = $this->response()->header('Retry-After');

        return RetryAfterHelper::parse(is_string($header) ? $header : null);
    }
}
