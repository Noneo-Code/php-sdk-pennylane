<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerAccount;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('postLedgerAccounts', pointer: 'country_alpha2', source: SchemaSource::Request)]
enum CountryAlpha2: string
{
    case AT = 'AT';
    case BE = 'BE';
    case BG = 'BG';
    case CY = 'CY';
    case CZ = 'CZ';
    case DE = 'DE';
    case DK = 'DK';
    case EE = 'EE';
    case ES = 'ES';
    case FI = 'FI';
    case FR = 'FR';
    case GR = 'GR';
    case HR = 'HR';
    case HU = 'HU';
    case IE = 'IE';
    case IT = 'IT';
    case LT = 'LT';
    case LU = 'LU';
    case LV = 'LV';
    case MT = 'MT';
    case NL = 'NL';
    case PL = 'PL';
    case PT = 'PT';
    case RO = 'RO';
    case SE = 'SE';
    case SI = 'SI';
    case SK = 'SK';
    case GB = 'GB';
    case MC = 'MC';
    case CH = 'CH';
    case AD = 'AD';
    case MU = 'MU';
    case NO = 'NO';
    case XK = 'XK';
    case Any = 'any';
}
