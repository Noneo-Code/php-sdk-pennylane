<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutWebhookSubscription extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param list<WebhookEventType>|null $events */
    public function __construct(
        private readonly int $id,
        private readonly ?string $callbackUrl = null,
        private readonly ?array $events = null,
        private readonly ?bool $enabled = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/webhook_subscriptions/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'callback_url' => $this->callbackUrl,
            'events' => null !== $this->events ? array_map(static fn (WebhookEventType $event): string => $event->value, $this->events) : null,
            'enabled' => $this->enabled,
        ]);
    }
}
