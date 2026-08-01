<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Category;

enum CategoryDirection: string
{
    case CashIn = 'cash_in';
    case CashOut = 'cash_out';
}
