<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CommercialDocument;

enum CommercialDocumentType: string
{
    case Proforma = 'proforma';
    case ShippingOrder = 'shipping_order';
    case PurchasingOrder = 'purchasing_order';
}
