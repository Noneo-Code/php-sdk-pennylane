<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

enum DirectDebitMethod: string
{
    case GoCardless = 'GoCardless';
    case Xml = 'XML';
    case ProAccount = 'ProAccount';
}
