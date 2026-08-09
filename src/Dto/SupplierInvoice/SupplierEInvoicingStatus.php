<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getSupplierInvoice', pointer: 'e_invoicing.status')]
enum SupplierEInvoicingStatus: string
{
    case WaitingForValidation = 'waiting_for_validation';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Disputed = 'disputed';
    case Refused = 'refused';
    case Collected = 'collected';
    case PartiallyCollected = 'partially_collected';
}
