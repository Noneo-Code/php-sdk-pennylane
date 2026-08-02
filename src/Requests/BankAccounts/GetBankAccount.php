<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\BankAccounts;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetBankAccount extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/bank_accounts/{$this->id}";
    }
}
