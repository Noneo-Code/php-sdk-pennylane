<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetWebhookSubscription extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/webhook_subscriptions/{$this->id}";
    }
}
