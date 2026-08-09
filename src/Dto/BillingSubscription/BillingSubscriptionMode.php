<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getBillingSubscription', pointer: 'mode')]
enum BillingSubscriptionMode: string
{
    case AwaitingValidation = 'awaiting_validation';
    case Finalized = 'finalized';
    case Email = 'email';
}
