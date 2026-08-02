<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use Noneo\Sdk\Pennylane\Dto\Export\ExportDto;
use Noneo\Sdk\Pennylane\Dto\Export\ExportFileDto;
use Noneo\Sdk\Pennylane\Dto\Export\ExportStatus;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Exports\ExportAnalyticalGeneralLedger;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ExportsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedCreationPayload(): array
    {
        return [
            'id' => 124,
            'status' => 'pending',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    /** @return array<string, mixed> */
    private function documentedReadyPayload(): array
    {
        return [
            'id' => 124,
            'file_url' => 'https://app.pennylane.com/my_export.xlsx',
            'status' => 'ready',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testExportFecReturnsAnExportDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedCreationPayload(), 201)]));

        $export = $pennylane->exports()->exportFec(periodStart: '2023-08-30', periodEnd: '2023-08-30');

        self::assertInstanceOf(ExportDto::class, $export);
        self::assertSame(124, $export->id);
        self::assertSame(ExportStatus::Pending, $export->status);
    }

    public function testGetFecExportReturnsAnExportFileDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedReadyPayload())]));

        $export = $pennylane->exports()->getFecExport(124);

        self::assertInstanceOf(ExportFileDto::class, $export);
        self::assertSame('https://app.pennylane.com/my_export.xlsx', $export->fileUrl);
    }

    public function testExportGeneralLedgerReturnsAnExportDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedCreationPayload(), 201)]));

        $export = $pennylane->exports()->exportGeneralLedger(periodStart: '2023-08-30', periodEnd: '2023-08-30');

        self::assertInstanceOf(ExportDto::class, $export);
        self::assertSame(124, $export->id);
    }

    public function testGetGeneralLedgerExportReturnsAnExportFileDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedReadyPayload())]));

        $export = $pennylane->exports()->getGeneralLedgerExport(124);

        self::assertInstanceOf(ExportFileDto::class, $export);
    }

    public function testExportAnalyticalGeneralLedgerReturnsAnExportDtoAndForwardsMode(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedCreationPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $export = $pennylane->exports()->exportAnalyticalGeneralLedger(
            periodStart: '2023-08-30',
            periodEnd: '2023-08-30',
            mode: AnalyticalGeneralLedgerExportMode::InColumn,
        );

        self::assertInstanceOf(ExportDto::class, $export);
        $mockClient->assertSent(function (ExportAnalyticalGeneralLedger $sent): bool {
            return 'in_column' === ($sent->body()->all()['mode'] ?? null);
        });
    }

    public function testGetAnalyticalGeneralLedgerExportReturnsAnExportFileDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedReadyPayload())]));

        $export = $pennylane->exports()->getAnalyticalGeneralLedgerExport(124);

        self::assertInstanceOf(ExportFileDto::class, $export);
    }
}
