<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Journals\PostJournals;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class JournalsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedJournalPayload(): array
    {
        return ['id' => 7, 'code' => 'VT', 'label' => 'Ventes', 'type' => 'sale'];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedJournalPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->journals()->list(filters: FilterSet::make()->eq('type', 'sale'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals' === $response->getPendingRequest()->getUrl()
                && '[{"field":"type","operator":"eq","value":"sale"}]' === ($response->getPendingRequest()->query()->all()['filter'] ?? null);
        });

        self::assertInstanceOf(JournalDto::class, $page->items[0]);
        self::assertSame(7, $page->items[0]->id);
    }

    public function testGetSendsAGetRequestToTheJournalEndpoint(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedJournalPayload())]);
        $firm->withMockClient($mockClient);

        $journal = $firm->company(1234)->journals()->get(7);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals/7' === $response->getPendingRequest()->getUrl();
        });

        self::assertSame('VT', $journal->code);
    }

    public function testCreateSendsAPostRequestWithTheJournalBody(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedJournalPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->journals()->create('VT', 'Ventes');

        $mockClient->assertSent(function (PostJournals $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals' === $response->getPendingRequest()->getUrl()
                && ['code' => 'VT', 'label' => 'Ventes'] === $sent->body()->all();
        });
    }
}
