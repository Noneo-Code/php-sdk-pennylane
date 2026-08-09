<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Me;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getMe', pointer: 'user.locale')]
enum Locale: string
{
    case Fr = 'fr';
    case En = 'en';
    case De = 'de';
}
