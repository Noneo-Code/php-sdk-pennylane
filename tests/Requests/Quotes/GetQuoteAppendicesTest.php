<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuoteAppendices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetQuoteAppendicesTest extends TestCase
{
    public function testSendsAGetRequestToTheAppendicesEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetQuoteAppendices(42));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/quotes/42/appendices' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetQuoteAppendices(42));
    }
}
