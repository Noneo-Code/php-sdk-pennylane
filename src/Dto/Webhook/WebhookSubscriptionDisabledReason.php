<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Webhook;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getWebhookSubscription', pointer: 'disabled_reason')]
enum WebhookSubscriptionDisabledReason: string
{
    case PermanentError = 'permanent_error';
    case RepeatedFailure = 'repeated_failure';
    case Manual = 'manual';
}
