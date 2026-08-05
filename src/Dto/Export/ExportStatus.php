<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getFecExport', pointer: 'status')]
enum ExportStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Error = 'error';
}
