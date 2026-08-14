<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Customers;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCustomerCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCustomerCategoriesTest extends TestCase
{
    public function testSendsAGetRequestToTheCustomerCategoriesEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetCustomerCategories(42));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/customers/42/categories' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorAndLimitAsQueryParameters(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetCustomerCategories(42, cursor: 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2', limit: 50));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
                'limit' => 50,
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetCustomerCategories(42));
    }
}
