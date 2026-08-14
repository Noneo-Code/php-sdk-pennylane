<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BillingSubscriptions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\GetBillingSubscription;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetBillingSubscriptionTest extends TestCase
{
    public function testSendsAGetRequestToTheBillingSubscriptionEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetBillingSubscription(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/billing_subscriptions/42' === $sent->resolveEndpoint();
        });
    }
}
