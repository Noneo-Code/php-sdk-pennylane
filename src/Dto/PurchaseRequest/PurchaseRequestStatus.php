<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PurchaseRequest;

enum PurchaseRequestStatus: string
{
    case ToBeValidated = 'to_be_validated';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Invoiced = 'invoiced';
}
