<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CommercialDocument;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getCommercialDocument', pointer: 'document_type')]
enum CommercialDocumentType: string
{
    case Proforma = 'proforma';
    case ShippingOrder = 'shipping_order';
    case PurchasingOrder = 'purchasing_order';
}
