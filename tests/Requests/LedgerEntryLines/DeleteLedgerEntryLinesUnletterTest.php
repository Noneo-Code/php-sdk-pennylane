<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\UnbalancedLetteringStrategy;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\DeleteLedgerEntryLinesUnletter;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class DeleteLedgerEntryLinesUnletterTest extends TestCase
{
    public function testSendsADeleteRequestWithTheLetteringStrategyAndLines(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make('', 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new DeleteLedgerEntryLinesUnletter(UnbalancedLetteringStrategy::Partial, [3455]));

        $mockClient->assertSent(function (DeleteLedgerEntryLinesUnletter $sent): bool {
            return Method::DELETE === $sent->getMethod()
                && '/ledger_entry_lines/lettering' === $sent->resolveEndpoint()
                && [
                    'unbalanced_lettering_strategy' => 'partial',
                    'ledger_entry_lines' => [['id' => 3455]],
                ] === $sent->body()->all();
        });
    }
}
