<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Transaction;

enum InvoiceType: string
{
    case Customer = 'customer';
    case Supplier = 'supplier';
}
