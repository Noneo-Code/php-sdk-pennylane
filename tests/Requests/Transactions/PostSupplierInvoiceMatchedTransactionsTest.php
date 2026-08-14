<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Transactions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Transactions\PostSupplierInvoiceMatchedTransactions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostSupplierInvoiceMatchedTransactionsTest extends TestCase
{
    public function testSendsAPostRequestWithTheTransactionId(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostSupplierInvoiceMatchedTransactions(supplierInvoiceId: 1234, transactionId: 42));

        $mockClient->assertSent(function (PostSupplierInvoiceMatchedTransactions $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/supplier_invoices/1234/matched_transactions' === $sent->resolveEndpoint()
                && ['transaction_id' => 42] === $sent->body()->all();
        });
    }
}
