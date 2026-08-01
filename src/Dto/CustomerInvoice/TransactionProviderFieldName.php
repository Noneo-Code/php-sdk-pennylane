<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

enum TransactionProviderFieldName: string
{
    case PaymentId = 'payment_id';
    case ChargeId = 'charge_id';
    case ReportId = 'report_id';
    case Webid = 'webid';
    case Label = 'label';
}
