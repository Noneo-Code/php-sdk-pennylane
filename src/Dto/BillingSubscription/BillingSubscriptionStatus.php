<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

enum BillingSubscriptionStatus: string
{
    case Draft = 'draft';
    case Stopped = 'stopped';
    case Finished = 'finished';
    case Pending = 'pending';
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
}
