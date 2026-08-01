<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

enum CustomerType: string
{
    case Company = 'company';
    case Individual = 'individual';
}
