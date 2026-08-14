<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogEntryDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class ChangelogsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedEntryPayload(): array
    {
        return ['id' => 1, 'operation' => 'update', 'processed_at' => 'now', 'updated_at' => 'now', 'created_at' => 'now'];
    }

    public function testLedgerEntryLinesSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->changelogs()->ledgerEntryLines();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/changelogs/ledger_entry_lines' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testDmsFilesSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->changelogs()->dmsFiles();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/changelogs/dms_files' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testSupplierInvoicesSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->changelogs()->supplierInvoices(cursor: 'abc', limit: 50, startDate: '2026-01-01T00:00:00Z');

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $query = $response->getPendingRequest()->query()->all();

            return Method::GET === $sent->getMethod()
                && 'https://app.pennylane.com/api/external/firm/v1/companies/1234/changelogs/supplier_invoices' === $response->getPendingRequest()->getUrl()
                && 'abc' === $query['cursor']
                && 50 === $query['limit']
                && '2026-01-01T00:00:00Z' === $query['start_date'];
        });

        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testCustomerInvoicesSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->changelogs()->customerInvoices();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && 'https://app.pennylane.com/api/external/firm/v1/companies/1234/changelogs/customer_invoices' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testPaginateSupplierInvoicesTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $entries = iterator_to_array($firm->company(1234)->changelogs()->paginateSupplierInvoices());

        self::assertCount(1, $entries);
        self::assertSame(1, $entries[0]->id);
    }

    public function testPaginateCustomerInvoicesTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $entries = iterator_to_array($firm->company(1234)->changelogs()->paginateCustomerInvoices());

        self::assertCount(1, $entries);
        self::assertSame(1, $entries[0]->id);
    }
}
