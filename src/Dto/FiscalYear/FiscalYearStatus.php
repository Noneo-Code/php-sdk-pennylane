<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\FiscalYear;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('postFiscalYears', pointer: 'status')]
enum FiscalYearStatus: string
{
    case Open = 'open';
    case Reopen = 'reopen';
    case Closed = 'closed';
    case Frozen = 'frozen';
}
