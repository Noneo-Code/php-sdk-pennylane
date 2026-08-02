<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class DeleteSupplierInvoiceMatchedTransactions extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly int $supplierInvoiceId,
        private readonly int $transactionId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->supplierInvoiceId}/matched_transactions/{$this->transactionId}";
    }
}
