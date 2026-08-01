<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Products;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Products\GetProducts;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetProductsTest extends TestCase
{
    public function testSendsAGetRequestToTheProductsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetProducts());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/products' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitFilterAndSortAsQueryParameters(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetProducts(
            cursor: 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
            limit: 50,
            filters: FilterSet::make()->eq('reference', 'REF-123'),
            sort: Sort::desc('id'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
                'limit' => 50,
                'filter' => '[{"field":"reference","operator":"eq","value":"REF-123"}]',
                'sort' => '-id',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetProducts());
    }
}
