<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getQuote', pointer: 'language')]
enum BillingLanguage: string
{
    case FrFr = 'fr_FR';
    case EnGb = 'en_GB';
    case DeDe = 'de_DE';
}
