<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getProAccountMandateMigrations', pointer: 'items.direct_debit_method')]
enum DirectDebitMethod: string
{
    case GoCardless = 'GoCardless';
    case Xml = 'XML';
    case ProAccount = 'ProAccount';
}
