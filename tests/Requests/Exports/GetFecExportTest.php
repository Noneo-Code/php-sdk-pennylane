<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Exports;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Exports\GetFecExport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class GetFecExportTest extends TestCase
{
    public function testSendsAGetRequestToTheFecExportEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'file_url' => 'https://app.pennylane.com/my_export.xlsx',
            'status' => 'ready',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetFecExport(124));

        $mockClient->assertSent(function (GetFecExport $sent): bool {
            return Method::GET === $sent->getMethod() && '/exports/fecs/124' === $sent->resolveEndpoint();
        });
    }
}
