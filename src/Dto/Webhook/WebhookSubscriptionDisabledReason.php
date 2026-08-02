<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Webhook;

enum WebhookSubscriptionDisabledReason: string
{
    case PermanentError = 'permanent_error';
    case RepeatedFailure = 'repeated_failure';
    case Manual = 'manual';
}
