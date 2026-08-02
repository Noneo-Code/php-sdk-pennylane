<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Webhook\CreatedWebhookSubscriptionDto;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookSubscriptionDto;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class WebhookSubscriptionsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 12345,
            'callback_url' => 'https://example.com/webhooks',
            'events' => ['dms_file.created'],
            'enabled' => true,
            'disabled_at' => null,
            'disabled_reason' => null,
            'last_failure_status' => null,
            'last_failure_at' => null,
            'consecutive_failures' => 0,
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testCreateReturnsACreatedWebhookSubscriptionDtoWithTheSecret(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload() + ['secret' => 'whsec_a1b2c3'], 201)]);
        $pennylane->withMockClient($mockClient);

        $dto = $pennylane->webhookSubscriptions()->create(
            callbackUrl: 'https://example.com/webhooks',
            events: [WebhookEventType::DmsFileCreated],
        );

        self::assertInstanceOf(CreatedWebhookSubscriptionDto::class, $dto);
        self::assertSame('whsec_a1b2c3', $dto->secret);
    }

    public function testListReturnsACursorPageOfWebhookSubscriptionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->webhookSubscriptions()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(WebhookSubscriptionDto::class, $page->items[0]);
        self::assertSame(12345, $page->items[0]->id);
    }

    public function testPaginateYieldsEveryItemAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([
            MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => true, 'next_cursor' => 'page-2']),
            MockResponse::make(['items' => [['id' => 999] + $this->documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->withMockClient($mockClient);

        $items = iterator_to_array($pennylane->webhookSubscriptions()->paginate());

        self::assertCount(2, $items);
        self::assertInstanceOf(WebhookSubscriptionDto::class, $items[0]);
        self::assertInstanceOf(WebhookSubscriptionDto::class, $items[1]);
    }

    public function testGetReturnsAWebhookSubscriptionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $dto = $pennylane->webhookSubscriptions()->get(12345);

        self::assertInstanceOf(WebhookSubscriptionDto::class, $dto);
        self::assertSame(12345, $dto->id);
    }

    public function testUpdateReturnsAWebhookSubscriptionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['enabled' => false] + $this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $dto = $pennylane->webhookSubscriptions()->update(12345, enabled: false);

        self::assertInstanceOf(WebhookSubscriptionDto::class, $dto);
        self::assertFalse($dto->enabled);
    }

    public function testDeleteSendsTheDeleteRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(status: 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->webhookSubscriptions()->delete(12345);

        $mockClient->assertSentCount(1);
    }
}
