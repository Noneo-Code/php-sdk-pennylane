<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\ProAccountMandates;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\GetProAccountMandateMigrations;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetProAccountMandateMigrationsTest extends TestCase
{
    public function testSendsAGetRequestToTheMandateMigrationsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetProAccountMandateMigrations(
            cursor: 'abc',
            limit: 10,
            filters: FilterSet::make()->eq('status', 'available'),
            sort: Sort::desc('id'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/pro_account/mandate_migrations' === $sent->resolveEndpoint()
                && [
                    'cursor' => 'abc',
                    'limit' => 10,
                    'filter' => '[{"field":"status","operator":"eq","value":"available"}]',
                    'sort' => '-id',
                ] === $response->getPendingRequest()->query()->all();
        });
    }
}
