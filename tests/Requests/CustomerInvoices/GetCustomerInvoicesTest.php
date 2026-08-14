<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCustomerInvoicesTest extends TestCase
{
    public function testSendsAGetRequestToTheCustomerInvoicesEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetCustomerInvoices());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/customer_invoices' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitFilterAndSortAsQueryParameters(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetCustomerInvoices(
            cursor: 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
            limit: 50,
            filters: FilterSet::make()->eq('draft', 'true'),
            sort: Sort::desc('date'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
                'limit' => 50,
                'filter' => '[{"field":"draft","operator":"eq","value":"true"}]',
                'sort' => '-date',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetCustomerInvoices());
    }
}
