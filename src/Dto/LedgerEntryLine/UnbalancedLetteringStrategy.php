<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntryLine;

enum UnbalancedLetteringStrategy: string
{
    case None = 'none';
    case Partial = 'partial';
}
