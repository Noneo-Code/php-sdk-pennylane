<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\TrialBalance\TrialBalanceLineDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\TrialBalance\GetTrialBalance;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class TrialBalanceTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedTrialBalanceLinePayload(): array
    {
        return [
            'number' => '512',
            'formatted_number' => '51200000',
            'label' => 'Capital souscrit - appelé, versé',
            'debits' => '45353.95',
            'credits' => '2343.05',
        ];
    }

    public function testListReturnsACursorPageOfTrialBalanceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedTrialBalanceLinePayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
        ]));

        $page = $pennylane->trialBalance()->list(periodStart: '2022-01-01', periodEnd: '2022-12-31');

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertTrue($page->hasMore);
        self::assertSame('cursor-2', $page->nextCursor);
        self::assertCount(1, $page->items);
        self::assertInstanceOf(TrialBalanceLineDto::class, $page->items[0]);
        self::assertSame('512', $page->items[0]->number);
    }

    public function testListForwardsThePeriodAndIsAuxiliary(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([
            MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->withMockClient($mockClient);

        $pennylane->trialBalance()->list(periodStart: '2022-01-01', periodEnd: '2022-12-31', isAuxiliary: true);

        $mockClient->assertSent(function (GetTrialBalance $sent, Response $response): bool {
            return [
                'period_start' => '2022-01-01',
                'period_end' => '2022-12-31',
                'is_auxiliary' => true,
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testPaginateYieldsTrialBalanceLineDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedTrialBalanceLinePayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [[
                    'number' => '607',
                    'formatted_number' => '60700000',
                    'label' => 'Achats de marchandises',
                    'debits' => '100.00',
                    'credits' => '0.00',
                ]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $lines = iterator_to_array($pennylane->trialBalance()->paginate(periodStart: '2022-01-01', periodEnd: '2022-12-31'));

        self::assertCount(2, $lines);
        self::assertSame('512', $lines[0]->number);
        self::assertSame('607', $lines[1]->number);
    }
}
