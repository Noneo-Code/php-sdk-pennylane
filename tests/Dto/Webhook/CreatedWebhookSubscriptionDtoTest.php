<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Webhook;

use Noneo\Sdk\Pennylane\Dto\Webhook\CreatedWebhookSubscriptionDto;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class CreatedWebhookSubscriptionDtoTest extends TestCase
{
    public function testBuildsFromTheDocumentedCreationPayload(): void
    {
        $dto = CreatedWebhookSubscriptionDto::fromArray([
            'id' => 12345,
            'callback_url' => 'https://example.com/webhooks',
            'events' => ['dms_file.created', 'customer_invoice.e_invoicing_status_updated'],
            'enabled' => true,
            'secret' => 'whsec_a1b2c3',
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ]);

        self::assertSame(12345, $dto->id);
        self::assertSame('https://example.com/webhooks', $dto->callbackUrl);
        self::assertSame([WebhookEventType::DmsFileCreated, WebhookEventType::CustomerInvoiceEInvoicingStatusUpdated], $dto->events);
        self::assertTrue($dto->enabled);
        self::assertSame('whsec_a1b2c3', $dto->secret);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->createdAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->updatedAt);
    }
}
