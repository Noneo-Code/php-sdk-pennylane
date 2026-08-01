<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SupplierInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetSupplierInvoice extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->id}";
    }
}
