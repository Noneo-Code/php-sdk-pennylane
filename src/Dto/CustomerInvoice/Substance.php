<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

enum Substance: string
{
    case Goods = 'goods';
    case Services = 'services';
}
