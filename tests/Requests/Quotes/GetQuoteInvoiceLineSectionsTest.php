<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuoteInvoiceLineSections;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetQuoteInvoiceLineSectionsTest extends TestCase
{
    public function testSendsAGetRequestToTheInvoiceLineSectionsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetQuoteInvoiceLineSections(42, sort: Sort::asc('rank')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/quotes/42/invoice_line_sections' === $sent->resolveEndpoint()
                && ['sort' => 'rank'] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetQuoteInvoiceLineSections(42));
    }
}
