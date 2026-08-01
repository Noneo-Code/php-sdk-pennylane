<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

enum EInvoiceStatusTransition: string
{
    case Disputed = 'disputed';
    case Refused = 'refused';
    case Approved = 'approved';
}
