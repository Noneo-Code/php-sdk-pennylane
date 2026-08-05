<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Category;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getCategory', pointer: 'direction')]
enum CategoryDirection: string
{
    case CashIn = 'cash_in';
    case CashOut = 'cash_out';
}
