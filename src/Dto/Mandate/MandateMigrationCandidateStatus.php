<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getProAccountMandateMigrations', pointer: 'items.status')]
enum MandateMigrationCandidateStatus: string
{
    case Available = 'available';
    case Completed = 'completed';
}
