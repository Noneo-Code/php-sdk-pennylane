<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

enum ExportStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Error = 'error';
}
