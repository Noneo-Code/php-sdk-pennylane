<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('exportAnalyticalGeneralLedger', pointer: 'mode', source: SchemaSource::Request)]
enum AnalyticalGeneralLedgerExportMode: string
{
    case InLine = 'in_line';
    case InColumn = 'in_column';
}
