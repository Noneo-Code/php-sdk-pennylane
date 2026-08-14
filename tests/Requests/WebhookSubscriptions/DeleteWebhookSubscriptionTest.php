<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\WebhookSubscriptions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\DeleteWebhookSubscription;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class DeleteWebhookSubscriptionTest extends TestCase
{
    public function testSendsADeleteRequestToTheWebhookSubscriptionEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(status: 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new DeleteWebhookSubscription(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::DELETE === $sent->getMethod()
                && '/webhook_subscriptions/42' === $sent->resolveEndpoint();
        });
    }
}
