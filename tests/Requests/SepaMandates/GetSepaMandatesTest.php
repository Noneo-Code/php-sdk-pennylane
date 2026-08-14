<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\GetSepaMandates;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetSepaMandatesTest extends TestCase
{
    public function testSendsAGetRequestToTheSepaMandatesEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetSepaMandates(
            cursor: 'abc',
            limit: 10,
            filters: FilterSet::make()->eq('customer_id', 42),
            sort: Sort::desc('id'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/sepa_mandates' === $sent->resolveEndpoint()
                && [
                    'cursor' => 'abc',
                    'limit' => 10,
                    'filter' => '[{"field":"customer_id","operator":"eq","value":42}]',
                    'sort' => '-id',
                ] === $response->getPendingRequest()->query()->all();
        });
    }
}
