<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Changelogs;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetDmsFilesChanges;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetDmsFilesChangesTest extends TestCase
{
    public function testSendsAGetRequestToTheDmsFilesChangelogEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new GetDmsFilesChanges(cursor: 'abc', limit: 10, startDate: '2025-06-25T11:54:18Z'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/changelogs/dms_files' === $sent->resolveEndpoint()
                && [
                    'cursor' => 'abc',
                    'limit' => 10,
                    'start_date' => '2025-06-25T11:54:18Z',
                ] === $response->getPendingRequest()->query()->all();
        });
    }
}
