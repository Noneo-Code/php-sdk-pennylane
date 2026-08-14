<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\ProAccountMandates;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\GetProAccountMandates;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetProAccountMandatesTest extends TestCase
{
    public function testSendsAGetRequestToTheProAccountMandatesEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetProAccountMandates(
            cursor: 'abc',
            limit: 10,
            filters: FilterSet::make()->eq('status', 'enabled'),
            sort: Sort::desc('id'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/pro_account/mandates' === $sent->resolveEndpoint()
                && [
                    'cursor' => 'abc',
                    'limit' => 10,
                    'filter' => '[{"field":"status","operator":"eq","value":"enabled"}]',
                    'sort' => '-id',
                ] === $response->getPendingRequest()->query()->all();
        });
    }
}
