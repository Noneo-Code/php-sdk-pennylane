<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('updateSupplierInvoicePaymentStatus', pointer: 'payment_status', source: SchemaSource::Request)]
enum PaymentStatusUpdate: string
{
    case Paid = 'paid';
    case ToBePaid = 'to_be_paid';
}
