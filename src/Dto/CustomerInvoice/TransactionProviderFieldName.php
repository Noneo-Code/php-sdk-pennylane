<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('putSupplierInvoice', pointer: 'transaction_reference.provider_field_name', source: SchemaSource::Request)]
enum TransactionProviderFieldName: string
{
    case PaymentId = 'payment_id';
    case ChargeId = 'charge_id';
    case ReportId = 'report_id';
    case Webid = 'webid';
    case Label = 'label';
}
