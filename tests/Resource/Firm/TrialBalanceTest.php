<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\TrialBalance\TrialBalanceLineDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class TrialBalanceTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return ['number' => '512', 'formatted_number' => '51200000', 'label' => 'Banque', 'debits' => '45353.95', 'credits' => '2343.05'];
    }

    public function testListSendsAGetRequestScopedToTheCompanyWithTheRequiredPeriod(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [$this->documentedPayload()],
            'total_pages' => 1,
            'current_page' => 1,
            'total_items' => 1,
            'per_page' => 20,
        ])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->trialBalance()->list('2023-01-01', '2023-12-31');

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/trial_balance' === $response->getPendingRequest()->getUrl()
                && '2023-01-01' === ($response->getPendingRequest()->query()->all()['period_start'] ?? null);
        });

        self::assertInstanceOf(TrialBalanceLineDto::class, $page->items[0]);
        self::assertSame('512', $page->items[0]->number);
    }

    public function testPaginateTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [$this->documentedPayload()],
            'total_pages' => 1,
            'current_page' => 1,
            'total_items' => 1,
            'per_page' => 20,
        ])]);
        $firm->withMockClient($mockClient);

        $lines = iterator_to_array($firm->company(1234)->trialBalance()->paginate('2023-01-01', '2023-12-31'));

        self::assertCount(1, $lines);
        self::assertSame('512', $lines[0]->number);
    }
}
