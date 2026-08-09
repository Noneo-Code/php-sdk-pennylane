<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getQuote', pointer: 'discount.type')]
enum DiscountType: string
{
    case Absolute = 'absolute';
    case Relative = 'relative';
}
