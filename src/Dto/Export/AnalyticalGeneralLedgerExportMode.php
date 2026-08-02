<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

enum AnalyticalGeneralLedgerExportMode: string
{
    case InLine = 'in_line';
    case InColumn = 'in_column';
}
