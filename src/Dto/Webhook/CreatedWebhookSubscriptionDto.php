<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Webhook;

final readonly class CreatedWebhookSubscriptionDto
{
    /**
     * `$secret` est la clé de signature des webhooks, rendue une seule fois par
     * Pennylane à la création de l'abonnement : c'est le secret le plus durable
     * que le SDK manipule.
     *
     * @param list<WebhookEventType> $events
     */
    public function __construct(
        public int $id,
        public string $callbackUrl,
        public array $events,
        public bool $enabled,
        #[\SensitiveParameter] public string $secret,
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
        $secret = $body['secret'] ?? null;
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

        if (!is_string($secret)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "secret" textuel.');
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
            secret: $secret,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
