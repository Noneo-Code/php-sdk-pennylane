<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerAccounts;

use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateLedgerAccount extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly string|Clear|null $label = null,
        private readonly bool|Clear|null $letterable = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/ledger_accounts/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'label' => $this->label,
            'letterable' => $this->letterable,
        ]);
    }
}
