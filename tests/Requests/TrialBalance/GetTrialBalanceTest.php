<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\TrialBalance;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\TrialBalance\GetTrialBalance;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetTrialBalanceTest extends TestCase
{
    public function testSendsAGetRequestWithTheRequiredPeriod(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetTrialBalance(periodStart: '2022-01-01', periodEnd: '2022-12-31'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/trial_balance' === $sent->resolveEndpoint()
                && [
                    'period_start' => '2022-01-01',
                    'period_end' => '2022-12-31',
                ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsIsAuxiliaryCursorAndLimitAsQueryParameters(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetTrialBalance(
            periodStart: '2022-01-01',
            periodEnd: '2022-12-31',
            isAuxiliary: true,
            cursor: 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
            limit: 50,
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'period_start' => '2022-01-01',
                'period_end' => '2022-12-31',
                'is_auxiliary' => true,
                'cursor' => 'MjAyNS0wMS0wOVQwODoyNDozOC44MTI0NTha',
                'limit' => 50,
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetTrialBalance(periodStart: '2022-01-01', periodEnd: '2022-12-31'));
    }
}
