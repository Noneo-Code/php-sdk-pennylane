<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

enum PaymentConditions: string
{
    case UponReceipt = 'upon_receipt';
    case Custom = 'custom';
    case SevenDays = '7_days';
    case FifteenDays = '15_days';
    case ThirtyDays = '30_days';
    case ThirtyDaysEndOfMonth = '30_days_end_of_month';
    case FortyFiveDays = '45_days';
    case FortyFiveDaysEndOfMonth = '45_days_end_of_month';
    case SixtyDays = '60_days';
}
