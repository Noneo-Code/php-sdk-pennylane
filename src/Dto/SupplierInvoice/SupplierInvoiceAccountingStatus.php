<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

enum SupplierInvoiceAccountingStatus: string
{
    case Draft = 'draft';
    case Archived = 'archived';
    case Entry = 'entry';
    case ValidationNeeded = 'validation_needed';
    case Complete = 'complete';
}
