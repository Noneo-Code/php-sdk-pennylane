<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategorizedLedgerEntryLineDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\LedgerEntryLineDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\UnbalancedLetteringStrategy;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class LedgerEntryLinesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedLedgerEntryLinePayload(): array
    {
        return [
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'label' => 'Employees - Jean Dupont / Salary May 2023',
            'categories' => [],
            'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42'],
            'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/42'],
            'date' => '2023-08-30',
            'ledger_entry' => ['id' => 123001],
            'lettered_ledger_entry_lines' => [
                'ids' => [42, 1271004],
                'url' => 'https://app.pennylane.com/api/external/v2/ledger_entry_lines/42/lettered_ledger_entry_lines',
            ],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListReturnsACursorPageOfLedgerEntryLineDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedLedgerEntryLinePayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->ledgerEntryLines()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(LedgerEntryLineDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testGetReturnsALedgerEntryLineDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedLedgerEntryLinePayload())]));

        $line = $pennylane->ledgerEntryLines()->get(42);

        self::assertInstanceOf(LedgerEntryLineDto::class, $line);
        self::assertSame(123001, $line->ledgerEntryId);
    }

    public function testCategoriesReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [[
                    'id' => 421,
                    'label' => 'HR - Salaries',
                    'weight' => '0.25',
                    'category_group' => ['id' => 229],
                    'analytical_code' => 'CODE123',
                    'created_at' => '2023-08-30T10:08:08.146343Z',
                    'updated_at' => '2023-08-30T10:08:08.146343Z',
                ]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->ledgerEntryLines()->categories(42);

        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(421, $page->items[0]->id);
    }

    public function testCategorizeReturnsACategorizedLedgerEntryLineDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make([
                'ledger_entry_line' => [
                    'id' => 2,
                    'label' => 'Employee - remuneration and contributions',
                    'categories' => [[
                        'id' => 421,
                        'label' => 'HR - Salaries',
                        'weight' => '0.25',
                        'category_group' => ['id' => 229],
                        'analytical_code' => 'CODE123',
                        'created_at' => '2023-08-30T10:08:08.146343Z',
                        'updated_at' => '2023-08-30T10:08:08.146343Z',
                    ]],
                ],
            ]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        $result = $pennylane->ledgerEntryLines()->categorize(2, [new CategoryAllocation(421, '0.25')]);

        self::assertInstanceOf(CategorizedLedgerEntryLineDto::class, $result);
        self::assertSame(2, $result->id);
        self::assertCount(1, $result->categories);
    }

    public function testLetterReturnsTheLetteredLedgerEntryLineIds(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make([['id' => 42], ['id' => 1271004]])]));

        $ids = $pennylane->ledgerEntryLines()->letter(UnbalancedLetteringStrategy::None, [42, 1271004]);

        self::assertSame([42, 1271004], $ids);
    }

    public function testUnletterSendsTheDeleteRequest(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make('', 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->ledgerEntryLines()->unletter(UnbalancedLetteringStrategy::None, [42]);

        $mockClient->assertSentCount(1);
    }

    public function testLetteredReturnsACursorPageOfLedgerEntryLineDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedLedgerEntryLinePayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->ledgerEntryLines()->lettered(42);

        self::assertInstanceOf(LedgerEntryLineDto::class, $page->items[0]);
    }

    public function testPaginateYieldsLedgerEntryLineDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedLedgerEntryLinePayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $lines = iterator_to_array($pennylane->ledgerEntryLines()->paginate());

        self::assertCount(1, $lines);
        self::assertSame(42, $lines[0]->id);
    }
}
