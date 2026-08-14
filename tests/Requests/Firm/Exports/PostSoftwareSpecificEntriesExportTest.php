<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Exports;

use Noneo\Sdk\Pennylane\Dto\Export\AccountNumbersType;
use Noneo\Sdk\Pennylane\Dto\Export\SoftwareSpecificExportType;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostSoftwareSpecificEntriesExport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostSoftwareSpecificEntriesExportTest extends TestCase
{
    public function testSendsAPostRequestWithTheMinimalRequiredBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new PostSoftwareSpecificEntriesExport(SoftwareSpecificExportType::Acd, '2023-01-01', '2023-01-31'));

        $mockClient->assertSent(function (PostSoftwareSpecificEntriesExport $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/exports/software_specific_entries' === $sent->resolveEndpoint()
                && [
                    'export_type' => 'acd',
                    'period_start' => '2023-01-01',
                    'period_end' => '2023-01-31',
                ] === $sent->body()->all();
        });
    }

    public function testForwardsOptionalFieldsInTheBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new PostSoftwareSpecificEntriesExport(
            SoftwareSpecificExportType::Agiris,
            '2023-01-01',
            '2023-01-31',
            journalCodes: ['VT', 'AC'],
            includeAttachments: true,
            accountNumbersType: AccountNumbersType::Pennylane,
        ));

        $mockClient->assertSent(function (PostSoftwareSpecificEntriesExport $sent): bool {
            return [
                'export_type' => 'agiris',
                'period_start' => '2023-01-01',
                'period_end' => '2023-01-31',
                'journal_codes' => ['VT', 'AC'],
                'include_attachments' => true,
                'account_numbers_type' => 'pennylane',
            ] === $sent->body()->all();
        });
    }
}
