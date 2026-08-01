<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuoteInvoiceLines;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetQuoteInvoiceLinesTest extends TestCase
{
    public function testSendsAGetRequestToTheInvoiceLinesEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetQuoteInvoiceLines(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/quotes/42/invoice_lines' === $sent->resolveEndpoint();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetQuoteInvoiceLines(42));
    }
}
