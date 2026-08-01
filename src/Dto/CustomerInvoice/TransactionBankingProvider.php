<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

enum TransactionBankingProvider: string
{
    case Stripe = 'stripe';
    case Gocardless = 'gocardless';
    case Bank = 'bank';
    case Budgetinsight = 'budgetinsight';
}
