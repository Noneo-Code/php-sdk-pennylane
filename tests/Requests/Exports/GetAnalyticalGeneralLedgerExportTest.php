<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Exports;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Exports\GetAnalyticalGeneralLedgerExport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class GetAnalyticalGeneralLedgerExportTest extends TestCase
{
    public function testSendsAGetRequestToTheAnalyticalGeneralLedgerExportEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'file_url' => 'https://app.pennylane.com/my_export.xlsx',
            'status' => 'ready',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetAnalyticalGeneralLedgerExport(124));

        $mockClient->assertSent(function (GetAnalyticalGeneralLedgerExport $sent): bool {
            return Method::GET === $sent->getMethod() && '/exports/analytical_general_ledgers/124' === $sent->resolveEndpoint();
        });
    }
}
