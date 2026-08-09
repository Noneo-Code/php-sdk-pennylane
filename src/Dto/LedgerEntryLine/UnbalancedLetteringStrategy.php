<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('postLedgerEntryLinesLetter', pointer: 'unbalanced_lettering_strategy', source: SchemaSource::Request)]
enum UnbalancedLetteringStrategy: string
{
    case None = 'none';
    case Partial = 'partial';
}
