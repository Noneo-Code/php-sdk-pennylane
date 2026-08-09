<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Transaction;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getTransactionMatchedInvoices', pointer: 'items.type')]
enum InvoiceType: string
{
    case Customer = 'customer';
    case Supplier = 'supplier';
}
