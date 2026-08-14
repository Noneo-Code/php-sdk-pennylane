<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearDto;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\FiscalYears\GetFiscalYears;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class FiscalYearsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedFiscalYearPayload(): array
    {
        return [
            'id' => 12,
            'start' => '2023-08-30',
            'finish' => '2022-12-31',
            'status' => 'open',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListReturnsACursorPageOfFiscalYearDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedFiscalYearPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
        ]));

        $page = $pennylane->fiscalYears()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertTrue($page->hasMore);
        self::assertSame('cursor-2', $page->nextCursor);
        self::assertCount(1, $page->items);
        self::assertInstanceOf(FiscalYearDto::class, $page->items[0]);
        self::assertSame(12, $page->items[0]->id);
    }

    public function testListForwardsSort(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->fiscalYears()->list(sort: Sort::asc('start'));

        $mockClient->assertSent(function (GetFiscalYears $sent, Response $response): bool {
            return 'start' === ($response->getPendingRequest()->query()->all()['sort'] ?? null);
        });
    }

    public function testPaginateYieldsFiscalYearDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedFiscalYearPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [[
                    'id' => 13,
                    'start' => '2024-01-01',
                    'finish' => '2024-12-31',
                    'status' => 'closed',
                    'created_at' => '2023-08-30T10:08:08.146343Z',
                    'updated_at' => '2023-08-30T10:08:08.146343Z',
                ]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $fiscalYears = iterator_to_array($pennylane->fiscalYears()->paginate());

        self::assertCount(2, $fiscalYears);
        self::assertSame(12, $fiscalYears[0]->id);
        self::assertSame(13, $fiscalYears[1]->id);
    }
}
