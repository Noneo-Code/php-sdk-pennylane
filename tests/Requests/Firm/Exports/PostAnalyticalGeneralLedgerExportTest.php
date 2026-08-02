<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Exports;

use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostAnalyticalGeneralLedgerExport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostAnalyticalGeneralLedgerExportTest extends TestCase
{
    public function testSendsAPostRequestWithThePeriodBody(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->withMockClient($mockClient);

        $firm->send(new PostAnalyticalGeneralLedgerExport('2023-01-01', '2023-01-31'));

        $mockClient->assertSent(function (PostAnalyticalGeneralLedgerExport $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/exports/analytical_general_ledgers' === $sent->resolveEndpoint()
                && ['period_start' => '2023-01-01', 'period_end' => '2023-01-31'] === $sent->body()->all();
        });
    }

    public function testForwardsTheOptionalMode(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1, 'status' => 'pending', 'created_at' => 'now', 'updated_at' => 'now'])]);
        $firm->withMockClient($mockClient);

        $firm->send(new PostAnalyticalGeneralLedgerExport('2023-01-01', '2023-01-31', AnalyticalGeneralLedgerExportMode::InColumn));

        $mockClient->assertSent(function (PostAnalyticalGeneralLedgerExport $sent): bool {
            return 'in_column' === ($sent->body()->all()['mode'] ?? null);
        });
    }
}
