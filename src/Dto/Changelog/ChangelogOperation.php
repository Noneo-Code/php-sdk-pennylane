<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Changelog;

enum ChangelogOperation: string
{
    case Insert = 'insert';
    case Update = 'update';
    case Delete = 'delete';
}
