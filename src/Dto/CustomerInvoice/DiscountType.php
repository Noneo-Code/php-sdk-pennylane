<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

enum DiscountType: string
{
    case Absolute = 'absolute';
    case Relative = 'relative';
}
