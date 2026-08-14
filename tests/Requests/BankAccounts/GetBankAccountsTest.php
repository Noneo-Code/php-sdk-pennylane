<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BankAccounts;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\GetBankAccounts;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetBankAccountsTest extends TestCase
{
    public function testSendsAGetRequestToTheBankAccountsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetBankAccounts(cursor: 'abc', limit: 10, sort: Sort::desc('id')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/bank_accounts' === $sent->resolveEndpoint()
                && ['cursor' => 'abc', 'limit' => 10, 'sort' => '-id'] === $response->getPendingRequest()->query()->all();
        });
    }
}
