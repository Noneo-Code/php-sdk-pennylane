<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Transactions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Transactions\CreateTransaction;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CreateTransactionTest extends TestCase
{
    public function testSendsAPostRequestToTheTransactionsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreateTransaction(
            bankAccountId: 42,
            label: 'test label',
            date: '2025-07-30',
            amount: '120.00',
            fee: '12.00',
        ));

        $mockClient->assertSent(function (CreateTransaction $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/transactions' === $sent->resolveEndpoint()
                && [
                    'bank_account_id' => 42,
                    'label' => 'test label',
                    'date' => '2025-07-30',
                    'amount' => '120.00',
                    'fee' => '12.00',
                ] === $sent->body()->all();
        });
    }

    public function testOmitsTheOptionalFeeFromTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreateTransaction(bankAccountId: 42, label: 'test label', date: '2025-07-30', amount: '120.00'));

        $mockClient->assertSent(function (CreateTransaction $sent): bool {
            return [
                'bank_account_id' => 42,
                'label' => 'test label',
                'date' => '2025-07-30',
                'amount' => '120.00',
            ] === $sent->body()->all();
        });
    }
}
