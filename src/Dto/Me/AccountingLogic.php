<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Me;

enum AccountingLogic: string
{
    case FrPcg = 'FR_PCG';
    case FrAgriculture = 'FR_AGRICULTURE';
    case FrNonProfit = 'FR_NON_PROFIT';
    case FrNotary = 'FR_NOTARY';
    case DeSkr03 = 'DE_SKR03';
    case DeSkr04 = 'DE_SKR04';
}
