<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Webhook;

enum WebhookEventType: string
{
    case CustomerInvoiceEInvoicingStatusUpdated = 'customer_invoice.e_invoicing_status_updated';
    case DmsFileCreated = 'dms_file.created';
}
