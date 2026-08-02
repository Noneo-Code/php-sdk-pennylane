<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

enum MandateMigrationCandidateStatus: string
{
    case Available = 'available';
    case Completed = 'completed';
}
