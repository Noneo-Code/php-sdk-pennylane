<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class MarkAsPaidCustomerInvoice extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}/mark_as_paid";
    }
}
