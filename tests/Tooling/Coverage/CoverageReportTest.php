<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\CoverageReport;
use Noneo\Sdk\Pennylane\Tooling\Coverage\RequestSignature;
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

        $report = new CoverageReport($operations, signatures: []);

        self::assertSame(2, $report->total());
        self::assertSame(0, $report->coveredCount());
        self::assertSame($operations, $report->missing());
    }

    public function testMatchesAnOperationOnItsMethodAndPath(): void
    {
        $operations = [
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
            new SpecOperation('POST', '/api/external/v2/journals', 'postJournals'),
        ];

        $report = new CoverageReport($operations, signatures: [
            new RequestSignature('GetJournals', 'GET', '/journals'),
        ]);

        self::assertSame(1, $report->coveredCount());
        self::assertSame('postJournals', $report->missing()[0]->operationId);
    }

    public function testDoesNotCountAWellNamedRequestThatTargetsTheWrongPath(): void
    {
        $operations = [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')];

        $report = new CoverageReport($operations, signatures: [
            new RequestSignature('GetJournals', 'GET', '/journal'),
        ]);

        self::assertSame(0, $report->coveredCount());
    }

    public function testDoesNotCountAMatchingPathSentWithTheWrongMethod(): void
    {
        $operations = [new SpecOperation('POST', '/api/external/v2/journals', 'postJournals')];

        $report = new CoverageReport($operations, signatures: [
            new RequestSignature('PostJournals', 'GET', '/journals'),
        ]);

        self::assertSame(0, $report->coveredCount());
    }

    public function testCoversAnOperationWhoseIdDoesNotFollowTheClassNamingConvention(): void
    {
        $operations = [new SpecOperation('GET', '/api/external/v2/fiscal_years', 'company-fiscal-years')];

        $report = new CoverageReport($operations, signatures: [
            new RequestSignature('GetFiscalYears', 'GET', '/fiscal_years'),
        ]);

        self::assertSame(1, $report->coveredCount());
        self::assertSame([], $report->missing());
    }

    public function testCoversAFirmOperationThroughItsCompanyScopedEndpoint(): void
    {
        $operations = [
            new SpecOperation('GET', '/api/external/firm/v1/companies/{company_id}/trial_balance', 'company-trial-balance'),
        ];

        $report = new CoverageReport($operations, signatures: [
            new RequestSignature('GetTrialBalance', 'GET', '/trial_balance'),
        ]);

        self::assertSame(1, $report->coveredCount());
    }

    public function testMatchesPathParametersRegardlessOfTheirName(): void
    {
        $operations = [
            new SpecOperation('PUT', '/api/external/v2/supplier_invoices/{supplier_invoice_id}/categories', 'putSupplierInvoiceCategories'),
        ];

        $report = new CoverageReport($operations, signatures: [
            new RequestSignature('PutSupplierInvoiceCategories', 'PUT', '/supplier_invoices/{}/categories'),
        ]);

        self::assertSame(1, $report->coveredCount());
    }

    public function testExcludesADocumentedOperationFromTheTotalAndFromMissing(): void
    {
        $operations = [
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
            new SpecOperation('POST', '/api/external/v2/ledger_attachments', 'postLedgerAttachments'),
        ];

        $report = new CoverageReport(
            $operations,
            signatures: [new RequestSignature('GetJournals', 'GET', '/journals')],
            excludedOperationIds: ['postLedgerAttachments' => 'Déprécié au profit de postFileAttachments.'],
        );

        self::assertSame(1, $report->total());
        self::assertSame(1, $report->coveredCount());
        self::assertSame([], $report->missing());
        self::assertCount(1, $report->excluded());
        self::assertSame('postLedgerAttachments', $report->excluded()[0]['operation']->operationId);
        self::assertSame('Déprécié au profit de postFileAttachments.', $report->excluded()[0]['reason']);
    }

    public function testReportsRequestClassesThatMatchNoDocumentedOperation(): void
    {
        $report = new CoverageReport(
            [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')],
            signatures: [
                new RequestSignature('GetJournals', 'GET', '/journals'),
                new RequestSignature('GetGhosts', 'GET', '/ghosts'),
            ],
        );

        self::assertCount(1, $report->undocumented());
        self::assertSame('GetGhosts', $report->undocumented()[0]->basename);
    }
}
