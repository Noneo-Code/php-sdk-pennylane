<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class FinalizeCustomerInvoice extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}/finalize";
    }
}
