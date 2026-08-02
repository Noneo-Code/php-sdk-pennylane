<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Changelogs;

final class GetLedgerEntriesCategoryChanges extends ChangelogRequest
{
    public function resolveEndpoint(): string
    {
        return '/changelogs/ledger_entries_categories';
    }
}
