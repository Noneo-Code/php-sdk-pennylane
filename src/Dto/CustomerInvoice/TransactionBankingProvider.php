<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('putSupplierInvoice', pointer: 'transaction_reference.banking_provider', source: SchemaSource::Request)]
enum TransactionBankingProvider: string
{
    case Stripe = 'stripe';
    case Gocardless = 'gocardless';
    case Bank = 'bank';
    case Budgetinsight = 'budgetinsight';
}
