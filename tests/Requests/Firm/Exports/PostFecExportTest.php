<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Exports;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostFecExport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostFecExportTest extends TestCase
{
    public function testSendsAPostRequestWithThePeriodBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new PostFecExport('2023-01-01', '2023-01-31'));

        $mockClient->assertSent(function (PostFecExport $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/exports/fecs' === $sent->resolveEndpoint()
                && ['period_start' => '2023-01-01', 'period_end' => '2023-01-31'] === $sent->body()->all();
        });
    }
}
