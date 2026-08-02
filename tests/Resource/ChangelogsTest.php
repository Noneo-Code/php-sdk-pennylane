<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogEntryDto;
use Noneo\Sdk\Pennylane\Dto\Changelog\LedgerEntryCategoryChangelogEntryDto;
use Noneo\Sdk\Pennylane\Dto\Changelog\LedgerEntryLineCategoryChangelogEntryDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Resource\Changelogs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ChangelogsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function uniformEntryPayload(): array
    {
        return [
            'id' => 1,
            'operation' => 'insert',
            'processed_at' => '2025-06-25T11:54:18Z',
            'updated_at' => '2025-06-25T11:54:18Z',
            'created_at' => '2025-06-25T11:54:18Z',
        ];
    }

    private function pennylaneWith(mixed $body): Pennylane
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$body], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        return $pennylane;
    }

    /** @return list<array{0: \Closure(Changelogs): CursorPage}> */
    public static function uniformEndpointMethods(): array
    {
        return [
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->customerInvoices()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->customers()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->ledgerEntryLines()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->products()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->quotes()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->supplierInvoices()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->suppliers()],
            [static fn (Changelogs $changelogs): CursorPage => $changelogs->transactions()],
        ];
    }

    /** @param \Closure(Changelogs): CursorPage $call */
    #[DataProvider('uniformEndpointMethods')]
    public function testListReturnsAChangelogEntryDtoPage(\Closure $call): void
    {
        $pennylane = $this->pennylaneWith($this->uniformEntryPayload());

        $page = $call($pennylane->changelogs());

        self::assertCount(1, $page->items);
        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testLedgerEntriesCategoriesReturnsALedgerEntryCategoryChangelogEntryDtoPage(): void
    {
        $pennylane = $this->pennylaneWith([
            'id' => 1,
            'operation' => 'update',
            'ledger_entry' => ['id' => 42, 'type' => 'general'],
            'processed_at' => '2025-06-25T11:54:18Z',
            'updated_at' => '2025-06-25T11:54:18Z',
            'created_at' => '2025-06-25T11:54:18Z',
        ]);

        $page = $pennylane->changelogs()->ledgerEntriesCategories();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(LedgerEntryCategoryChangelogEntryDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->ledgerEntryId);
    }

    public function testLedgerEntryLinesCategoriesReturnsALedgerEntryLineCategoryChangelogEntryDtoPage(): void
    {
        $pennylane = $this->pennylaneWith([
            'id' => 1,
            'operation' => 'delete',
            'ledger_entry_line' => null,
            'ledger_entry' => null,
            'processed_at' => '2025-06-25T11:54:18Z',
            'updated_at' => '2025-06-25T11:54:18Z',
            'created_at' => '2025-06-25T11:54:18Z',
        ]);

        $page = $pennylane->changelogs()->ledgerEntryLinesCategories();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(LedgerEntryLineCategoryChangelogEntryDto::class, $page->items[0]);
        self::assertNull($page->items[0]->ledgerEntryId);
    }

    public function testPaginateCustomerInvoicesYieldsEveryItemAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([
            MockResponse::make(['items' => [$this->uniformEntryPayload()], 'has_more' => true, 'next_cursor' => 'page-2']),
            MockResponse::make(['items' => [['id' => 2] + $this->uniformEntryPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->withMockClient($mockClient);

        $items = iterator_to_array($pennylane->changelogs()->paginateCustomerInvoices());

        self::assertCount(2, $items);
        self::assertInstanceOf(ChangelogEntryDto::class, $items[0]);
        self::assertInstanceOf(ChangelogEntryDto::class, $items[1]);
    }
}
