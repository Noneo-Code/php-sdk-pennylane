<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getGocardlessMandate', pointer: 'status')]
enum GocardlessMandateStatus: string
{
    case PendingCustomerApproval = 'pending_customer_approval';
    case PendingSubmission = 'pending_submission';
    case Submitted = 'submitted';
    case Active = 'active';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Consumed = 'consumed';
    case Replaced = 'replaced';
    case Blocked = 'blocked';
    case BankDisconnected = 'bank_disconnected';
}
