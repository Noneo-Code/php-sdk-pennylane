<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceAppendices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCustomerInvoiceAppendicesTest extends TestCase
{
    public function testSendsAGetRequestToTheAppendicesEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCustomerInvoiceAppendices(42, cursor: 'abc', limit: 10));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/customer_invoices/42/appendices' === $sent->resolveEndpoint()
                && ['cursor' => 'abc', 'limit' => 10] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetCustomerInvoiceAppendices(42));
    }
}
