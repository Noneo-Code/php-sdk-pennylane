<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Webhook;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;

final readonly class WebhookEvent
{
    /**
     * @param WebhookEventType|null $event              type reconnu, ou `null` si Pennylane
     *                                                  a publié un événement postérieur à
     *                                                  cette version du SDK
     * @param string                $eventName          valeur brute du champ `event`,
     *                                                  toujours renseignée
     * @param mixed[]               $object
     * @param mixed[]|null          $previousAttributes
     */
    public function __construct(
        public int $id,
        public ?WebhookEventType $event,
        public string $eventName,
        public int $retryCount,
        public int $createdAt,
        public string $companyId,
        public ?string $firmId,
        public array $object,
        public ?array $previousAttributes,
    ) {
    }

    /** @param mixed[] $payload */
    public static function fromArray(array $payload): self
    {
        $id = $payload['id'] ?? null;
        $event = $payload['event'] ?? null;
        $retryCount = $payload['retry_count'] ?? null;
        $createdAt = $payload['created'] ?? null;
        $data = $payload['data'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un entier "id".');
        }

        if (!is_string($event)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un "event" textuel.');
        }

        if (!is_int($retryCount)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un entier "retry_count".');
        }

        if (!is_int($createdAt)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un entier "created".');
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un objet "data".');
        }

        $context = $data['context'] ?? null;
        $object = $data['object'] ?? null;
        $previousAttributes = $data['previous_attributes'] ?? null;

        if (!is_array($context) || !is_string($context['company_id'] ?? null)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un "data.context.company_id" textuel.');
        }

        if (!is_array($object)) {
            throw new \InvalidArgumentException('L\'événement webhook Pennylane doit contenir un objet "data.object".');
        }

        if (null !== $previousAttributes && !is_array($previousAttributes)) {
            throw new \InvalidArgumentException('Le "data.previous_attributes" de l\'événement webhook Pennylane doit être un objet ou nul.');
        }

        return new self(
            id: $id,
            event: WebhookEventType::tryFrom($event),
            eventName: $event,
            retryCount: $retryCount,
            createdAt: $createdAt,
            companyId: $context['company_id'],
            firmId: is_string($context['firm_id'] ?? null) ? $context['firm_id'] : null,
            object: $object,
            previousAttributes: $previousAttributes,
        );
    }
}
