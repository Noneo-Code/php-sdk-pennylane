<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostWebhookSubscriptions extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param list<WebhookEventType> $events */
    public function __construct(
        private readonly string $callbackUrl,
        private readonly array $events,
        private readonly ?bool $enabled = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/webhook_subscriptions';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'callback_url' => $this->callbackUrl,
            'events' => array_map(static fn (WebhookEventType $event): string => $event->value, $this->events),
            'enabled' => $this->enabled,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
