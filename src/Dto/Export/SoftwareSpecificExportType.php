<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

#[DocumentedBy('postSoftwareSpecificEntriesExport', pointer: 'export_type', source: SchemaSource::Request)]
enum SoftwareSpecificExportType: string
{
    case Acd = 'acd';
    case Agiris = 'agiris';
}
