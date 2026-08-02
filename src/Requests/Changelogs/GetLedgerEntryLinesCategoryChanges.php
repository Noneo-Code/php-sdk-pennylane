<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Changelogs;

final class GetLedgerEntryLinesCategoryChanges extends ChangelogRequest
{
    public function resolveEndpoint(): string
    {
        return '/changelogs/ledger_entry_lines_categories';
    }
}
