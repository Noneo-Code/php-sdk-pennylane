<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

enum SepaMandateSequenceType: string
{
    case Frst = 'FRST';
    case Ooff = 'OOFF';
    case Rcur = 'RCUR';
    case Fnal = 'FNAL';
}
