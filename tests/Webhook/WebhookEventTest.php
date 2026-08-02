<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Webhook;

use Noneo\Sdk\Pennylane\Webhook\WebhookEvent;
use PHPUnit\Framework\TestCase;

final class WebhookEventTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 987654,
            'event' => 'customer_invoice.e_invoicing_status_updated',
            'retry_count' => 0,
            'created' => 1782864000,
            'data' => [
                'context' => [
                    'company_id' => 'abc-123',
                    'firm_id' => 'firm-456',
                ],
                'object' => [
                    'id' => 42,
                    'e_invoicing' => ['status' => 'accepted'],
                ],
                'previous_attributes' => [
                    'e_invoicing' => ['status' => 'submitted'],
                ],
            ],
        ];
    }

    public function testBuildsFromTheDocumentedEnvelope(): void
    {
        $event = WebhookEvent::fromArray($this->documentedPayload());

        self::assertSame(987654, $event->id);
        self::assertSame('customer_invoice.e_invoicing_status_updated', $event->event);
        self::assertSame(0, $event->retryCount);
        self::assertSame(1782864000, $event->createdAt);
        self::assertSame('abc-123', $event->companyId);
        self::assertSame('firm-456', $event->firmId);
        self::assertSame(['id' => 42, 'e_invoicing' => ['status' => 'accepted']], $event->object);
        self::assertSame(['e_invoicing' => ['status' => 'submitted']], $event->previousAttributes);
    }

    public function testFirmIdAndPreviousAttributesAreNullWhenAbsent(): void
    {
        $event = WebhookEvent::fromArray([
            'id' => 987654,
            'event' => 'dms_file.created',
            'retry_count' => 0,
            'created' => 1782864000,
            'data' => [
                'context' => ['company_id' => 'abc-123'],
                'object' => ['id' => 7],
            ],
        ]);

        self::assertNull($event->firmId);
        self::assertNull($event->previousAttributes);
    }
}
