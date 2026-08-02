<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Transactions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Transactions\DeleteSupplierInvoiceMatchedTransactions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class DeleteSupplierInvoiceMatchedTransactionsTest extends TestCase
{
    public function testSendsADeleteRequestToTheMatchedTransactionEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new DeleteSupplierInvoiceMatchedTransactions(supplierInvoiceId: 1234, transactionId: 42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::DELETE === $sent->getMethod() && '/supplier_invoices/1234/matched_transactions/42' === $sent->resolveEndpoint();
        });
    }
}
