<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\CoverageReport;
use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperation;
use PHPUnit\Framework\TestCase;

final class CoverageReportTest extends TestCase
{
    public function testReportsZeroCoverageWhenNoRequestClassExists(): void
    {
        $operations = [
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
            new SpecOperation('POST', '/api/external/v2/journals', 'postJournals'),
        ];

        $report = new CoverageReport($operations, coveredBasenames: []);

        self::assertSame(2, $report->total());
        self::assertSame(0, $report->coveredCount());
        self::assertSame($operations, $report->missing());
    }

    public function testMatchesARequestClassBasenameToItsOperationId(): void
    {
        $operations = [
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
            new SpecOperation('POST', '/api/external/v2/journals', 'postJournals'),
        ];

        $report = new CoverageReport($operations, coveredBasenames: ['GetJournals']);

        self::assertSame(1, $report->coveredCount());
        self::assertSame('postJournals', $report->missing()[0]->operationId);
    }

    public function testExcludesADocumentedOperationFromTheTotalAndFromMissing(): void
    {
        $operations = [
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
            new SpecOperation('POST', '/api/external/v2/ledger_attachments', 'postLedgerAttachments'),
        ];

        $report = new CoverageReport(
            $operations,
            coveredBasenames: ['GetJournals'],
            excludedOperationIds: ['postLedgerAttachments' => 'Déprécié au profit de postFileAttachments.'],
        );

        self::assertSame(1, $report->total());
        self::assertSame(1, $report->coveredCount());
        self::assertSame([], $report->missing());
        self::assertCount(1, $report->excluded());
        self::assertSame('postLedgerAttachments', $report->excluded()[0]['operation']->operationId);
        self::assertSame('Déprécié au profit de postFileAttachments.', $report->excluded()[0]['reason']);
    }
}
