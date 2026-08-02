<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntries;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\UpdateLedgerEntryLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\PutLedgerEntries;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutLedgerEntriesTest extends TestCase
{
    public function testSendsAPutRequestWithOnlyTheProvidedFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutLedgerEntries(id: 1, label: 'Nouveau libellé'));

        $mockClient->assertSent(function (PutLedgerEntries $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/ledger_entries/1' === $sent->resolveEndpoint()
                && ['label' => 'Nouveau libellé'] === $sent->body()->all();
        });
    }

    public function testForwardsCreateUpdateAndDeleteLedgerEntryLines(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutLedgerEntries(
            id: 1,
            createLines: [new NewLedgerEntryLine(debit: '50.00', credit: '0.00', ledgerAccountId: 987)],
            updateLines: [new UpdateLedgerEntryLine(id: 42, debit: '150.00')],
            deleteLineIds: [43],
        ));

        $mockClient->assertSent(function (PutLedgerEntries $sent): bool {
            return [
                'ledger_entry_lines' => [
                    'create' => [['debit' => '50.00', 'credit' => '0.00', 'ledger_account_id' => 987]],
                    'update' => [['id' => 42, 'debit' => '150.00']],
                    'delete' => [['id' => 43]],
                ],
            ] === $sent->body()->all();
        });
    }
}
