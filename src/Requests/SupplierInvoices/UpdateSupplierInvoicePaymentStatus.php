<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentStatusUpdate;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateSupplierInvoicePaymentStatus extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $supplierInvoiceId,
        private readonly PaymentStatusUpdate $paymentStatus,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->supplierInvoiceId}/payment_status";
    }

    /** @return array{payment_status: string} */
    protected function defaultBody(): array
    {
        return [
            'payment_status' => $this->paymentStatus->value,
        ];
    }
}
