<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BankAccount;

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
