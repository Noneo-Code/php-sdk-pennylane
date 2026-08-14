<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\WebhookSubscriptions;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\PutWebhookSubscription;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutWebhookSubscriptionTest extends TestCase
{
    public function testSendsAPutRequestToTheWebhookSubscriptionEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutWebhookSubscription(
            id: 42,
            callbackUrl: 'https://example.com/hooks-v2',
            events: [WebhookEventType::DmsFileCreated],
            enabled: false,
        ));

        $mockClient->assertSent(function (PutWebhookSubscription $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/webhook_subscriptions/42' === $sent->resolveEndpoint()
                && [
                    'callback_url' => 'https://example.com/hooks-v2',
                    'events' => ['dms_file.created'],
                    'enabled' => false,
                ] === $sent->body()->all();
        });
    }

    public function testOmittedFieldsAreNotSent(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutWebhookSubscription(id: 42, enabled: true));

        $mockClient->assertSent(fn (PutWebhookSubscription $sent): bool => ['enabled' => true] === $sent->body()->all());
    }
}
