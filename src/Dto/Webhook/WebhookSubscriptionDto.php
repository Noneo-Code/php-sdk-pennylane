<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Webhook;

final readonly class WebhookSubscriptionDto
{
    /** @param list<WebhookEventType> $events */
    public function __construct(
        public int $id,
        public string $callbackUrl,
        public array $events,
        public bool $enabled,
        public ?string $disabledAt,
        public ?WebhookSubscriptionDisabledReason $disabledReason,
        public ?int $lastFailureStatus,
        public ?string $lastFailureAt,
        public int $consecutiveFailures,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $callbackUrl = $body['callback_url'] ?? null;
        $events = $body['events'] ?? null;
        $enabled = $body['enabled'] ?? null;
        $disabledReason = $body['disabled_reason'] ?? null;
        $consecutiveFailures = $body['consecutive_failures'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($callbackUrl)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "callback_url" textuelle.');
        }

        if (!is_array($events)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "events".');
        }

        if (!is_bool($enabled)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "enabled".');
        }

        if (null !== $disabledReason && !is_string($disabledReason)) {
            throw new \InvalidArgumentException('Le "disabled_reason" de la réponse Pennylane doit être textuel ou nul.');
        }

        if (!is_int($consecutiveFailures)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "consecutive_failures".');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            callbackUrl: $callbackUrl,
            events: array_map(
                static fn (mixed $event): WebhookEventType => WebhookEventType::from(is_string($event) ? $event : throw new \InvalidArgumentException('Les "events" de la réponse Pennylane doivent être des chaînes.')),
                array_values($events),
            ),
            enabled: $enabled,
            disabledAt: is_string($body['disabled_at'] ?? null) ? $body['disabled_at'] : null,
            disabledReason: null !== $disabledReason ? WebhookSubscriptionDisabledReason::from($disabledReason) : null,
            lastFailureStatus: is_int($body['last_failure_status'] ?? null) ? $body['last_failure_status'] : null,
            lastFailureAt: is_string($body['last_failure_at'] ?? null) ? $body['last_failure_at'] : null,
            consecutiveFailures: $consecutiveFailures,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
