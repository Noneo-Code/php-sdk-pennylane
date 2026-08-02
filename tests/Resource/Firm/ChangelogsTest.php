<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogEntryDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
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
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->changelogs()->ledgerEntryLines();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/changelogs/ledger_entry_lines' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testDmsFilesSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedEntryPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->changelogs()->dmsFiles();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/changelogs/dms_files' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(ChangelogEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }
}
