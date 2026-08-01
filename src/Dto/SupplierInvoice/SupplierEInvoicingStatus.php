<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

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
