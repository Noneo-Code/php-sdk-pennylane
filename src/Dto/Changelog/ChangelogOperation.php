<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Changelog;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getQuoteChanges', pointer: 'items.operation')]
enum ChangelogOperation: string
{
    case Insert = 'insert';
    case Update = 'update';
    case Delete = 'delete';
}
