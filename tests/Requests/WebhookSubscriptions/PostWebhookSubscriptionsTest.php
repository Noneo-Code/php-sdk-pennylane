<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\WebhookSubscriptions;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\PostWebhookSubscriptions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostWebhookSubscriptionsTest extends TestCase
{
    public function testSendsAPostRequestToTheWebhookSubscriptionsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostWebhookSubscriptions(
            callbackUrl: 'https://example.com/webhooks',
            events: [WebhookEventType::DmsFileCreated, WebhookEventType::CustomerInvoiceEInvoicingStatusUpdated],
            enabled: false,
        ));

        $mockClient->assertSent(function (PostWebhookSubscriptions $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/webhook_subscriptions' === $sent->resolveEndpoint()
                && [
                    'callback_url' => 'https://example.com/webhooks',
                    'events' => ['dms_file.created', 'customer_invoice.e_invoicing_status_updated'],
                    'enabled' => false,
                ] === $sent->body()->all();
        });
    }

    public function testEnabledDefaultsToNotBeingSentSoThePennylaneDefaultApplies(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostWebhookSubscriptions(
            callbackUrl: 'https://example.com/webhooks',
            events: [WebhookEventType::DmsFileCreated],
        ));

        $mockClient->assertSent(fn (PostWebhookSubscriptions $sent): bool => !array_key_exists('enabled', $sent->body()->all()));
    }
}
