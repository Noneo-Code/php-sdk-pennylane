<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Supplier;

enum SupplierDueDateRule: string
{
    case Days = 'days';
    case DaysOrEndOfMonth = 'days_or_end_of_month';
}
