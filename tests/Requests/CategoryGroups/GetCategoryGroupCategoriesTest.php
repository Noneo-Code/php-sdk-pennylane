<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CategoryGroups;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CategoryGroups\GetCategoryGroupCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCategoryGroupCategoriesTest extends TestCase
{
    public function testSendsAGetRequestToTheCategoryGroupCategoriesEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCategoryGroupCategories(42));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/category_groups/42/categories' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorAndLimitAsQueryParameters(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCategoryGroupCategories(42, cursor: 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2', limit: 50));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
                'limit' => 50,
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetCategoryGroupCategories(42));
    }
}
