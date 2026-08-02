<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\UnbalancedLetteringStrategy;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\PostLedgerEntryLinesLetter;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostLedgerEntryLinesLetterTest extends TestCase
{
    public function testSendsAPostRequestWithTheLetteringStrategyAndLines(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([['id' => 3455], ['id' => 3456]])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostLedgerEntryLinesLetter(UnbalancedLetteringStrategy::None, [3455, 3456]));

        $mockClient->assertSent(function (PostLedgerEntryLinesLetter $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/ledger_entry_lines/lettering' === $sent->resolveEndpoint()
                && [
                    'unbalanced_lettering_strategy' => 'none',
                    'ledger_entry_lines' => [['id' => 3455], ['id' => 3456]],
                ] === $sent->body()->all();
        });
    }
}
