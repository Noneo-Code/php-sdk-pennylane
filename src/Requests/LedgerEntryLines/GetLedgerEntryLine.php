<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerEntryLines;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetLedgerEntryLine extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/ledger_entry_lines/{$this->id}";
    }
}
