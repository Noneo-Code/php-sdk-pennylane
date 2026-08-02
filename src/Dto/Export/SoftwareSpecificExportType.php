<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

enum SoftwareSpecificExportType: string
{
    case Acd = 'acd';
    case Agiris = 'agiris';
}
