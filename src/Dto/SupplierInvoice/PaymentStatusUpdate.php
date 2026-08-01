<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

enum PaymentStatusUpdate: string
{
    case Paid = 'paid';
    case ToBePaid = 'to_be_paid';
}
