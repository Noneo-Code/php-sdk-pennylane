<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\TrialBalance;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\TrialBalance\GetTrialBalance;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetTrialBalanceTest extends TestCase
{
    /** @return array<string, mixed> */
    private function emptyPage(): array
    {
        return ['items' => [], 'total_pages' => 0, 'current_page' => 1, 'total_items' => 0, 'per_page' => 20];
    }

    public function testSendsAGetRequestWithTheRequiredPeriodParameters(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->emptyPage())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new GetTrialBalance('2023-01-01', '2023-12-31'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/trial_balance' === $sent->resolveEndpoint()
                && ['period_start' => '2023-01-01', 'period_end' => '2023-12-31'] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsIsAuxiliaryPageAndPerPageAsQueryParameters(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->emptyPage())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new GetTrialBalance('2023-01-01', '2023-12-31', isAuxiliary: true, page: 2, perPage: 40));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'period_start' => '2023-01-01',
                'period_end' => '2023-12-31',
                'is_auxiliary' => true,
                'page' => 2,
                'per_page' => 40,
            ] === $response->getPendingRequest()->query()->all();
        });
    }
}
