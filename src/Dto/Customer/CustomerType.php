<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getCustomer', pointer: 'customer_type')]
enum CustomerType: string
{
    case Company = 'company';
    case Individual = 'individual';
}
