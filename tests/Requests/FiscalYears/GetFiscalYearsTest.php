<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\FiscalYears;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\FiscalYears\GetFiscalYears;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetFiscalYearsTest extends TestCase
{
    public function testSendsAGetRequestToTheFiscalYearsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetFiscalYears());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/fiscal_years' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitAndSortAsQueryParameters(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetFiscalYears(
            cursor: 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
            limit: 50,
            sort: Sort::asc('start'),
        ));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'dXBkYXRlZF9hdDoxNjc0MTIzNDU2',
                'limit' => 50,
                'sort' => 'start',
            ] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetFiscalYears());
    }
}
