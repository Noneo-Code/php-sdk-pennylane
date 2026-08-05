<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getProduct', pointer: 'substance')]
enum AnnotatedSubstance: string
{
    case Goods = 'goods';
    case Services = 'services';
}
