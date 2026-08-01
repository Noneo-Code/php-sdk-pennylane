<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\PurchaseRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetPurchaseRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/purchase_requests/{$this->id}";
    }
}
