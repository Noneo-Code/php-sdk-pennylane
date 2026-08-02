<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostCustomerInvoiceMatchedTransactions extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $customerInvoiceId,
        private readonly int $transactionId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->customerInvoiceId}/matched_transactions";
    }

    /** @return array{transaction_id: int} */
    protected function defaultBody(): array
    {
        return ['transaction_id' => $this->transactionId];
    }
}
