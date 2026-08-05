<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getProduct', pointer: 'substance')]
enum Substance: string
{
    case Goods = 'goods';
    case Services = 'services';
}
