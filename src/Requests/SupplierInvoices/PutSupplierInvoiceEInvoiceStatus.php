<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceStatusTransition;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceDisputeReason;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceRefusalReason;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutSupplierInvoiceEInvoiceStatus extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $supplierInvoiceId,
        private readonly EInvoiceStatusTransition $status,
        private readonly SupplierEInvoiceDisputeReason|SupplierEInvoiceRefusalReason|null $reason = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->supplierInvoiceId}/e_invoice_status";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'status' => $this->status->value,
            'reason' => $this->reason,
        ]);
    }
}
