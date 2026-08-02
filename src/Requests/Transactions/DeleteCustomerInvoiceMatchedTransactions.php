<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class DeleteCustomerInvoiceMatchedTransactions extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly int $customerInvoiceId,
        private readonly int $transactionId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->customerInvoiceId}/matched_transactions/{$this->transactionId}";
    }
}
