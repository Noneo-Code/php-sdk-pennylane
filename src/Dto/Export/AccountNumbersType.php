<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('postSoftwareSpecificEntriesExport', pointer: 'account_numbers_type', source: SchemaSource::Request)]
enum AccountNumbersType: string
{
    case Original = 'original';
    case Pennylane = 'pennylane';
}
