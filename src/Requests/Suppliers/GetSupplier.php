<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Suppliers;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetSupplier extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/suppliers/{$this->id}";
    }
}
