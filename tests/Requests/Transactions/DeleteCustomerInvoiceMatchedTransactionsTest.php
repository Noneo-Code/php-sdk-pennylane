<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Transactions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Transactions\DeleteCustomerInvoiceMatchedTransactions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class DeleteCustomerInvoiceMatchedTransactionsTest extends TestCase
{
    public function testSendsADeleteRequestToTheMatchedTransactionEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new DeleteCustomerInvoiceMatchedTransactions(customerInvoiceId: 1234, transactionId: 42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::DELETE === $sent->getMethod() && '/customer_invoices/1234/matched_transactions/42' === $sent->resolveEndpoint();
        });
    }
}
