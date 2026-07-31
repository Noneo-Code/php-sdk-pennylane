<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Filter;

enum FilterOperator: string
{
    case Eq = 'eq';
    case NotEq = 'not_eq';
    case Lt = 'lt';
    case Lteq = 'lteq';
    case Gt = 'gt';
    case Gteq = 'gteq';
    case In = 'in';
    case NotIn = 'not_in';
    case StartWith = 'start_with';
}
