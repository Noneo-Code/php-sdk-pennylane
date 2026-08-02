<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\FiscalYears;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\FiscalYears\GetFiscalYears;
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
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'total_pages' => 0, 'current_page' => 1, 'total_items' => 0, 'per_page' => 20])]);
        $firm->withMockClient($mockClient);

        $firm->send(new GetFiscalYears());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/fiscal_years' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsPageAndPerPageAsQueryParameters(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'total_pages' => 0, 'current_page' => 1, 'total_items' => 0, 'per_page' => 20])]);
        $firm->withMockClient($mockClient);

        $firm->send(new GetFiscalYears(page: 2, perPage: 40));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return ['page' => 2, 'per_page' => 40] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetFiscalYears());
    }
}
