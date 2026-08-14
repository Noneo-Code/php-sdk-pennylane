<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\PurchaseRequests;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\PurchaseRequests\GetPurchaseRequest;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetPurchaseRequestTest extends TestCase
{
    public function testSendsAGetRequestToThePurchaseRequestEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetPurchaseRequest(1));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/purchase_requests/1' === $sent->resolveEndpoint();
        });
    }
}
