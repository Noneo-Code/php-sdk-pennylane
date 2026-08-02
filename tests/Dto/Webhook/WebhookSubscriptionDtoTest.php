<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Webhook;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookSubscriptionDisabledReason;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookSubscriptionDto;
use PHPUnit\Framework\TestCase;

final class WebhookSubscriptionDtoTest extends TestCase
{
    public function testBuildsFromTheDocumentedPayload(): void
    {
        $dto = WebhookSubscriptionDto::fromArray([
            'id' => 12345,
            'callback_url' => 'https://example.com/webhooks',
            'events' => ['dms_file.created', 'customer_invoice.e_invoicing_status_updated'],
            'enabled' => true,
            'disabled_at' => null,
            'disabled_reason' => null,
            'last_failure_status' => null,
            'last_failure_at' => null,
            'consecutive_failures' => 3,
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ]);

        self::assertSame(12345, $dto->id);
        self::assertSame('https://example.com/webhooks', $dto->callbackUrl);
        self::assertSame([WebhookEventType::DmsFileCreated, WebhookEventType::CustomerInvoiceEInvoicingStatusUpdated], $dto->events);
        self::assertTrue($dto->enabled);
        self::assertNull($dto->disabledAt);
        self::assertNull($dto->disabledReason);
        self::assertNull($dto->lastFailureStatus);
        self::assertNull($dto->lastFailureAt);
        self::assertSame(3, $dto->consecutiveFailures);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->createdAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->updatedAt);
    }

    public function testParsesADisabledSubscription(): void
    {
        $dto = WebhookSubscriptionDto::fromArray([
            'id' => 12345,
            'callback_url' => 'https://example.com/webhooks',
            'events' => ['dms_file.created'],
            'enabled' => false,
            'disabled_at' => '2023-08-07T14:23:12.000Z',
            'disabled_reason' => 'repeated_failure',
            'last_failure_status' => 500,
            'last_failure_at' => '2023-08-07T14:20:00.000Z',
            'consecutive_failures' => 10,
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ]);

        self::assertFalse($dto->enabled);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->disabledAt);
        self::assertSame(WebhookSubscriptionDisabledReason::RepeatedFailure, $dto->disabledReason);
        self::assertSame(500, $dto->lastFailureStatus);
        self::assertSame('2023-08-07T14:20:00.000Z', $dto->lastFailureAt);
    }
}
