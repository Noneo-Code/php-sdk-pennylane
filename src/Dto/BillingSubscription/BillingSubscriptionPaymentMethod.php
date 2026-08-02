<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

enum BillingSubscriptionPaymentMethod: string
{
    case Offline = 'offline';
    case GocardlessDirectDebit = 'gocardless_direct_debit';
    case ProAccountSepaCore = 'pro_account_sepa_core';
}
