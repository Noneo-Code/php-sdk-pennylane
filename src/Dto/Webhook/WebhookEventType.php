<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Webhook;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getWebhookSubscription', pointer: 'events')]
enum WebhookEventType: string
{
    case CustomerInvoiceEInvoicingStatusUpdated = 'customer_invoice.e_invoicing_status_updated';
    case DmsFileCreated = 'dms_file.created';
}
