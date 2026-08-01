<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use Noneo\Sdk\Pennylane\Exception\PennylaneException;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

trait HandlesPennylaneErrors
{
    use AlwaysThrowOnErrors;

    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() >= 400;
    }

    public function getRequestException(Response $response, ?\Throwable $senderException): ?\Throwable
    {
        return PennylaneException::fromResponse($response);
    }
}
