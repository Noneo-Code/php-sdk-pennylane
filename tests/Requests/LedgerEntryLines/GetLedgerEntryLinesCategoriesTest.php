<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLinesCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetLedgerEntryLinesCategoriesTest extends TestCase
{
    public function testSendsAGetRequestToTheCategoriesOfALedgerEntryLine(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetLedgerEntryLinesCategories(42));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/ledger_entry_lines/42/categories' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitAndSortAsQueryParameters(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetLedgerEntryLinesCategories(42, cursor: 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha', limit: 10, sort: Sort::desc('id')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
                'limit' => 10,
                'sort' => '-id',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetLedgerEntryLinesCategories(42));
    }
}
