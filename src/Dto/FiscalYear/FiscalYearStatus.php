<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\FiscalYear;

enum FiscalYearStatus: string
{
    case Open = 'open';
    case Reopen = 'reopen';
    case Closed = 'closed';
    case Frozen = 'frozen';
}
