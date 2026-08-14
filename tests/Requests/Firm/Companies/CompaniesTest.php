<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Companies;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Companies\Companies;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class CompaniesTest extends TestCase
{
    public function testSendsAGetRequestToTheCompaniesEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'total_pages' => 0, 'current_page' => 1, 'total_items' => 0, 'per_page' => 20])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new Companies());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/companies' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsPagePerPageAndFilterAsQueryParameters(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'total_pages' => 0, 'current_page' => 1, 'total_items' => 0, 'per_page' => 20])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new Companies(page: 2, perPage: 40, filters: FilterSet::make()->eq('client_code', '42')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'page' => 2,
                'per_page' => 40,
                'filter' => '[{"field":"client_code","operator":"eq","value":"42"}]',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new Companies());
    }
}
