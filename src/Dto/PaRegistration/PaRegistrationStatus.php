<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PaRegistration;

enum PaRegistrationStatus: string
{
    case Pending = 'pending';
    case Activated = 'activated';
    case Provisioned = 'provisioned';
    case Draft = 'draft';
    case Archived = 'archived';
    case Conflict = 'conflict';
    case Ineligible = 'ineligible';
    case Lost = 'lost';
}
