<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getSupplierInvoice', pointer: 'accounting_status')]
enum SupplierInvoiceAccountingStatus: string
{
    case Draft = 'draft';
    case Archived = 'archived';
    case Entry = 'entry';
    case ValidationNeeded = 'validation_needed';
    case Complete = 'complete';
}
