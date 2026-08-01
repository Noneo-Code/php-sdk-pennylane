<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Products;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Products\GetProduct;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetProductTest extends TestCase
{
    public function testSendsAGetRequestToTheProductEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetProduct(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/products/42' === $sent->resolveEndpoint();
        });
    }
}
