<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('putSupplierInvoiceEInvoiceStatus', pointer: 'status=disputed.status', source: SchemaSource::Request)]
enum EInvoiceStatusTransition: string
{
    case Disputed = 'disputed';
    case Refused = 'refused';
    case Approved = 'approved';
}
