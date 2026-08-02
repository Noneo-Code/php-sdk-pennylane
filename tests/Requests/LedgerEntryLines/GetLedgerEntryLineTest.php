<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLine;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetLedgerEntryLineTest extends TestCase
{
    public function testSendsAGetRequestToTheLedgerEntryLineEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetLedgerEntryLine(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/ledger_entry_lines/42' === $sent->resolveEndpoint();
        });
    }
}
