<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BankAccount;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('postBankAccount', pointer: 'account_type', source: SchemaSource::Request)]
enum AccountType: string
{
    case Current = 'current';
    case Card = 'card';
    case Savings = 'savings';
    case Shares = 'shares';
    case Loan = 'loan';
    case LifeInsurance = 'life_insurance';
    case Other = 'other';
    case Checking = 'checking';
}
