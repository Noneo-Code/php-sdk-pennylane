<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getLedgerEntry', pointer: 'status')]
enum LedgerEntryStatus: string
{
    case AccountingNeeded = 'accounting_needed';
    case Archived = 'archived';
    case Complete = 'complete';
    case Draft = 'draft';
    case Entry = 'entry';
    case ValidationNeeded = 'validation_needed';
    case WaitingDetails = 'waiting_details';
}
