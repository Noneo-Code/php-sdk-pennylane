<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Supplier;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getSupplier', pointer: 'supplier_due_date_rule')]
enum SupplierDueDateRule: string
{
    case Days = 'days';
    case DaysOrEndOfMonth = 'days_or_end_of_month';
}
