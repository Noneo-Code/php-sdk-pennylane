<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerAccounts;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetLedgerAccount extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/ledger_accounts/{$this->id}";
    }
}
