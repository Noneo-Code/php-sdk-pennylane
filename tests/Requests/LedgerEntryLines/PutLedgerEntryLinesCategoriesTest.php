<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategoryAllocation;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\PutLedgerEntryLinesCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutLedgerEntryLinesCategoriesTest extends TestCase
{
    public function testSendsAPutRequestWithTheCategoryAllocations(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['ledger_entry_line' => ['id' => 2, 'label' => 'x', 'categories' => []]])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutLedgerEntryLinesCategories(2, [
            new CategoryAllocation(394, '0.6575'),
            new CategoryAllocation(421, '0.3425'),
        ]));

        $mockClient->assertSent(function (PutLedgerEntryLinesCategories $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/ledger_entry_lines/2/categories' === $sent->resolveEndpoint()
                && [
                    ['id' => 394, 'weight' => '0.6575'],
                    ['id' => 421, 'weight' => '0.3425'],
                ] === $sent->body()->all();
        });
    }

    public function testAnEmptyArrayOfAllocationsRemovesAllCategories(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['ledger_entry_line' => ['id' => 2, 'label' => 'x', 'categories' => []]])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutLedgerEntryLinesCategories(2, []));

        $mockClient->assertSent(function (PutLedgerEntryLinesCategories $sent): bool {
            return [] === $sent->body()->all();
        });
    }
}
