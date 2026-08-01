<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class SendToPaCustomerInvoice extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}/send_to_pa";
    }
}
