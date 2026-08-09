<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getSepaMandate', pointer: 'sequence_type')]
enum SepaMandateSequenceType: string
{
    case Frst = 'FRST';
    case Ooff = 'OOFF';
    case Rcur = 'RCUR';
    case Fnal = 'FNAL';
}
