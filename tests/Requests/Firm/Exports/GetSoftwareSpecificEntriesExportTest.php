<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Exports;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\GetSoftwareSpecificEntriesExport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetSoftwareSpecificEntriesExportTest extends TestCase
{
    public function testSendsAGetRequestToTheExportEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 124, 'file_url' => 'https://example.com/export.txt', 'status' => 'ready', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new GetSoftwareSpecificEntriesExport(124));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/exports/software_specific_entries/124' === $sent->resolveEndpoint();
        });
    }
}
