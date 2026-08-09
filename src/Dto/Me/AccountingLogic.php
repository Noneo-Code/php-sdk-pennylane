<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Me;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getMe', pointer: 'company.accounting_logic')]
enum AccountingLogic: string
{
    case FrPcg = 'FR_PCG';
    case FrAgriculture = 'FR_AGRICULTURE';
    case FrNonProfit = 'FR_NON_PROFIT';
    case FrNotary = 'FR_NOTARY';
    case DeSkr03 = 'DE_SKR03';
    case DeSkr04 = 'DE_SKR04';
}
