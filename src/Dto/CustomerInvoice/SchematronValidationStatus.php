<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getCustomerInvoice', pointer: 'schematron_validation_status')]
enum SchematronValidationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';
}
