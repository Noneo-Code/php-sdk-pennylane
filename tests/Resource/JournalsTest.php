<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
use Noneo\Sdk\Pennylane\Requests\Journals\PostJournals;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class JournalsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedJournalPayload(): array
    {
        return [
            'code' => 'RB',
            'id' => 42,
            'label' => 'Journal de Reprise Balance',
            'type' => 'miscellaneous',
        ];
    }

    public function testListReturnsACursorPageOfJournalDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedJournalPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
        ]));

        $page = $pennylane->journals()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertTrue($page->hasMore);
        self::assertSame('cursor-2', $page->nextCursor);
        self::assertCount(1, $page->items);
        self::assertInstanceOf(JournalDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testListForwardsFilterAndSort(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([
            MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->withMockClient($mockClient);

        $pennylane->journals()->list(filters: FilterSet::make()->eq('type', 'payroll'));

        $mockClient->assertSent(function (GetJournals $sent, Response $response): bool {
            return '[{"field":"type","operator":"eq","value":"payroll"}]' === ($response->getPendingRequest()->query()->all()['filter'] ?? null);
        });
    }

    public function testGetReturnsAJournalDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedJournalPayload())]));

        $journal = $pennylane->journals()->get(42);

        self::assertInstanceOf(JournalDto::class, $journal);
        self::assertSame('RB', $journal->code);
    }

    public function testCreateReturnsAJournalDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedJournalPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $journal = $pennylane->journals()->create(code: 'RB', label: 'Journal de Reprise Balance');

        self::assertInstanceOf(JournalDto::class, $journal);
        $mockClient->assertSent(function (PostJournals $sent): bool {
            return [
                'code' => 'RB',
                'label' => 'Journal de Reprise Balance',
            ] === $sent->body()->all();
        });
    }

    public function testPaginateYieldsJournalDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedJournalPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [['code' => 'AC', 'id' => 43, 'label' => 'Achats', 'type' => 'purchase']],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $journals = iterator_to_array($pennylane->journals()->paginate());

        self::assertCount(2, $journals);
        self::assertSame(42, $journals[0]->id);
        self::assertSame(43, $journals[1]->id);
    }
}
