<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Exports;

use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Exports\ExportAnalyticalGeneralLedger;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ExportAnalyticalGeneralLedgerTest extends TestCase
{
    public function testSendsAPostRequestWithThePeriodAndDefaultsModeToInLine(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'status' => 'pending',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new ExportAnalyticalGeneralLedger(periodStart: '2023-08-30', periodEnd: '2023-08-30'));

        $mockClient->assertSent(function (ExportAnalyticalGeneralLedger $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/exports/analytical_general_ledgers' === $sent->resolveEndpoint()
                && [
                    'period_start' => '2023-08-30',
                    'period_end' => '2023-08-30',
                ] === $sent->body()->all();
        });
    }

    public function testForwardsTheExplicitMode(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'status' => 'pending',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new ExportAnalyticalGeneralLedger(
            periodStart: '2023-08-30',
            periodEnd: '2023-08-30',
            mode: AnalyticalGeneralLedgerExportMode::InColumn,
        ));

        $mockClient->assertSent(function (ExportAnalyticalGeneralLedger $sent): bool {
            return [
                'period_start' => '2023-08-30',
                'period_end' => '2023-08-30',
                'mode' => 'in_column',
            ] === $sent->body()->all();
        });
    }
}
