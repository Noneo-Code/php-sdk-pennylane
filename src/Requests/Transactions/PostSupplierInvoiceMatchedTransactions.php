<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostSupplierInvoiceMatchedTransactions extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $supplierInvoiceId,
        private readonly int $transactionId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/supplier_invoices/{$this->supplierInvoiceId}/matched_transactions";
    }

    /** @return array{transaction_id: int} */
    protected function defaultBody(): array
    {
        return ['transaction_id' => $this->transactionId];
    }
}
