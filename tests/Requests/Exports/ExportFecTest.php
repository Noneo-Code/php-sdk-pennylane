<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Exports;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Exports\ExportFec;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ExportFecTest extends TestCase
{
    public function testSendsAPostRequestWithThePeriod(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'status' => 'pending',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new ExportFec(periodStart: '2023-08-30', periodEnd: '2023-08-30'));

        $mockClient->assertSent(function (ExportFec $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/exports/fecs' === $sent->resolveEndpoint()
                && [
                    'period_start' => '2023-08-30',
                    'period_end' => '2023-08-30',
                ] === $sent->body()->all();
        });
    }
}
