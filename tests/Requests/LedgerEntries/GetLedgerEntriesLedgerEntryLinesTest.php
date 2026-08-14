<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntries;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\GetLedgerEntriesLedgerEntryLines;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetLedgerEntriesLedgerEntryLinesTest extends TestCase
{
    public function testSendsAGetRequestToTheLedgerEntryLinesOfALedgerEntry(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetLedgerEntriesLedgerEntryLines(123001));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/ledger_entries/123001/ledger_entry_lines' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitAndFilterAsQueryParameters(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetLedgerEntriesLedgerEntryLines(
            123001,
            cursor: 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
            limit: 10,
            filters: FilterSet::make()->eq('ledger_account_id', 42),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
                'limit' => 10,
                'filter' => '[{"field":"ledger_account_id","operator":"eq","value":42}]',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetLedgerEntriesLedgerEntryLines(1));
    }
}
