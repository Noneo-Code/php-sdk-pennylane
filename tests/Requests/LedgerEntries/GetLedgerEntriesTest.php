<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntries;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\GetLedgerEntries;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetLedgerEntriesTest extends TestCase
{
    public function testSendsAGetRequestToTheLedgerEntriesEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetLedgerEntries());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/ledger_entries' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitFilterAndSortAsQueryParameters(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetLedgerEntries(
            cursor: 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
            limit: 50,
            filters: FilterSet::make()->eq('journal_id', 123),
            sort: Sort::desc('date'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
                'limit' => 50,
                'filter' => '[{"field":"journal_id","operator":"eq","value":123}]',
                'sort' => '-date',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetLedgerEntries());
    }
}
