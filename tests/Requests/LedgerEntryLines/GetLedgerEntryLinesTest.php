<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLines;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetLedgerEntryLinesTest extends TestCase
{
    public function testSendsAGetRequestToTheLedgerEntryLinesEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetLedgerEntryLines());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/ledger_entry_lines' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitFilterAndSortAsQueryParameters(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetLedgerEntryLines(
            cursor: 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
            limit: 20,
            filters: FilterSet::make()->gteq('date', '2024-01-01'),
            sort: Sort::asc('id'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
                'limit' => 20,
                'filter' => '[{"field":"date","operator":"gteq","value":"2024-01-01"}]',
                'sort' => 'id',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetLedgerEntryLines());
    }
}
