<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getCustomerInvoice', pointer: 'e_invoicing.status')]
enum EInvoicingStatus: string
{
    case Accepted = 'accepted';
    case Approved = 'approved';
    case Collected = 'collected';
    case InDispute = 'in_dispute';
    case PartiallyCollected = 'partially_collected';
    case Refused = 'refused';
    case Rejected = 'rejected';
    case Sent = 'sent';
    case Submitted = 'submitted';
}
