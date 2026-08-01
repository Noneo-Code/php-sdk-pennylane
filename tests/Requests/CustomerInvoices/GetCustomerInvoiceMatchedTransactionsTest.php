<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceMatchedTransactions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCustomerInvoiceMatchedTransactionsTest extends TestCase
{
    public function testSendsAGetRequestToTheMatchedTransactionsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCustomerInvoiceMatchedTransactions(42, cursor: 'abc', limit: 10, sort: Sort::asc('id')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/customer_invoices/42/matched_transactions' === $sent->resolveEndpoint()
                && ['cursor' => 'abc', 'limit' => 10, 'sort' => 'id'] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetCustomerInvoiceMatchedTransactions(42));
    }
}
