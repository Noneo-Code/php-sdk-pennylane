<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BankEstablishments;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BankEstablishments\GetBankEstablishments;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetBankEstablishmentsTest extends TestCase
{
    public function testSendsAGetRequestToTheBankEstablishmentsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetBankEstablishments(
            cursor: 'abc',
            limit: 10,
            filters: FilterSet::make()->eq('id', 1),
            sort: Sort::desc('id'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/bank_establishments' === $sent->resolveEndpoint()
                && [
                    'cursor' => 'abc',
                    'limit' => 10,
                    'filter' => '[{"field":"id","operator":"eq","value":1}]',
                    'sort' => '-id',
                ] === $response->getPendingRequest()->query()->all();
        });
    }
}
