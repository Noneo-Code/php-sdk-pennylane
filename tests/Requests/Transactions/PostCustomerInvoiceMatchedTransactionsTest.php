<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Transactions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Transactions\PostCustomerInvoiceMatchedTransactions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostCustomerInvoiceMatchedTransactionsTest extends TestCase
{
    public function testSendsAPostRequestWithTheTransactionId(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostCustomerInvoiceMatchedTransactions(customerInvoiceId: 1234, transactionId: 42));

        $mockClient->assertSent(function (PostCustomerInvoiceMatchedTransactions $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices/1234/matched_transactions' === $sent->resolveEndpoint()
                && ['transaction_id' => 42] === $sent->body()->all();
        });
    }
}
