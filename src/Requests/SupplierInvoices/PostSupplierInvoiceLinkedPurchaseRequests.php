<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SupplierInvoices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostSupplierInvoiceLinkedPurchaseRequests extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $supplierInvoiceId,
        private readonly int $purchaseRequestId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->supplierInvoiceId}/linked_purchase_requests";
    }

    /** @return array{purchase_request_id: int} */
    protected function defaultBody(): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequestId,
        ];
    }
}
