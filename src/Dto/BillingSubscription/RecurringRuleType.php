<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getBillingSubscription', pointer: 'recurring_rule.rule_type')]
enum RecurringRuleType: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}
