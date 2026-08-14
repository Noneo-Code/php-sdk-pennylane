<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Export\SoftwareSpecificExportType;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostSoftwareSpecificEntriesExport;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class ExportsTest extends TestCase
{
    public function testExportFecSendsAPostRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $export = $firm->company(1234)->exports()->exportFec('2023-01-01', '2023-01-31');

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/exports/fecs' === $response->getPendingRequest()->getUrl();
        });

        self::assertSame(1, $export->id);
    }

    public function testGetFecExportSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'file_url' => 'https://example.com/fec.txt', 'status' => 'ready', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->exports()->getFecExport(1);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/exports/fecs/1' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testExportAnalyticalGeneralLedgerSendsAPostRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->exports()->exportAnalyticalGeneralLedger('2023-01-01', '2023-01-31');

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/exports/analytical_general_ledgers' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testGetAnalyticalGeneralLedgerExportSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'file_url' => 'https://example.com/agl.csv', 'status' => 'ready', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->exports()->getAnalyticalGeneralLedgerExport(1);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/exports/analytical_general_ledgers/1' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testExportSoftwareSpecificEntriesSendsAPostRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $export = $firm->company(1234)->exports()->exportSoftwareSpecificEntries(SoftwareSpecificExportType::Acd, '2023-01-01', '2023-01-31');

        $mockClient->assertSent(function (PostSoftwareSpecificEntriesExport $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/exports/software_specific_entries' === $response->getPendingRequest()->getUrl()
                && 'acd' === ($sent->body()->all()['export_type'] ?? null);
        });

        self::assertSame(1, $export->id);
    }

    public function testGetSoftwareSpecificEntriesExportSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 124, 'file_url' => 'https://example.com/export.txt', 'status' => 'ready', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->exports()->getSoftwareSpecificEntriesExport(124);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/exports/software_specific_entries/124' === $response->getPendingRequest()->getUrl();
        });
    }
}
