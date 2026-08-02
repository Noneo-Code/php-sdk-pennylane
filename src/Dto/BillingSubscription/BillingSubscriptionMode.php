<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

enum BillingSubscriptionMode: string
{
    case AwaitingValidation = 'awaiting_validation';
    case Finalized = 'finalized';
    case Email = 'email';
}
