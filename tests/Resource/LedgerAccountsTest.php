<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\LedgerAccountDto;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\GetLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\PostLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\UpdateLedgerAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class LedgerAccountsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedLedgerAccountPayload(): array
    {
        return [
            'id' => 124,
            'number' => '512',
            'label' => 'Secondary Account',
            'vat_rate' => 'any',
            'country_alpha2' => 'FR',
            'enabled' => true,
            'type' => 'custom',
            'letterable' => false,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListReturnsACursorPageOfLedgerAccountDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedLedgerAccountPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
        ]));

        $page = $pennylane->ledgerAccounts()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertTrue($page->hasMore);
        self::assertSame('cursor-2', $page->nextCursor);
        self::assertCount(1, $page->items);
        self::assertInstanceOf(LedgerAccountDto::class, $page->items[0]);
        self::assertSame(124, $page->items[0]->id);
    }

    public function testListForwardsFilterAndSort(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->ledgerAccounts()->list(filters: FilterSet::make()->startWith('number', '411'));

        $mockClient->assertSent(function (GetLedgerAccounts $sent, Response $response): bool {
            return '[{"field":"number","operator":"start_with","value":"411"}]' === ($response->getPendingRequest()->query()->all()['filter'] ?? null);
        });
    }

    public function testGetReturnsALedgerAccountDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedLedgerAccountPayload())]));

        $ledgerAccount = $pennylane->ledgerAccounts()->get(124);

        self::assertInstanceOf(LedgerAccountDto::class, $ledgerAccount);
        self::assertSame('512', $ledgerAccount->number);
    }

    public function testCreateReturnsALedgerAccountDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedLedgerAccountPayload(), 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $ledgerAccount = $pennylane->ledgerAccounts()->create(
            number: '512',
            label: 'Secondary Account',
            vatRate: VatRate::FR_200,
            countryAlpha2: CountryAlpha2::FR,
        );

        self::assertInstanceOf(LedgerAccountDto::class, $ledgerAccount);
        $mockClient->assertSent(function (PostLedgerAccounts $sent): bool {
            return [
                'number' => '512',
                'label' => 'Secondary Account',
                'vat_rate' => 'FR_200',
                'country_alpha2' => 'FR',
            ] === $sent->body()->all();
        });
    }

    public function testUpdateReturnsALedgerAccountDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedLedgerAccountPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $ledgerAccount = $pennylane->ledgerAccounts()->update(124, letterable: true);

        self::assertInstanceOf(LedgerAccountDto::class, $ledgerAccount);
        $mockClient->assertSent(function (UpdateLedgerAccount $sent): bool {
            return ['letterable' => true] === $sent->body()->all();
        });
    }

    public function testPaginateYieldsLedgerAccountDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedLedgerAccountPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [[
                    'id' => 125,
                    'number' => '706000',
                    'label' => 'Ventes',
                    'vat_rate' => 'FR_200',
                    'country_alpha2' => 'FR',
                    'enabled' => true,
                    'type' => 'custom',
                    'letterable' => true,
                    'created_at' => '2023-08-30T10:08:08.146343Z',
                    'updated_at' => '2023-08-30T10:08:08.146343Z',
                ]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $ledgerAccounts = iterator_to_array($pennylane->ledgerAccounts()->paginate());

        self::assertCount(2, $ledgerAccounts);
        self::assertSame(124, $ledgerAccounts[0]->id);
        self::assertSame(125, $ledgerAccounts[1]->id);
    }
}
