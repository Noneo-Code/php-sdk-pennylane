<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SupplierInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class ValidateAccountingSupplierInvoice extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->id}/validate_accounting";
    }
}
