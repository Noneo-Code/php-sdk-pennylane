<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class CreateTransaction extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $bankAccountId,
        private readonly string $label,
        private readonly string $date,
        private readonly string $amount,
        private readonly ?string $fee = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/transactions';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'bank_account_id' => $this->bankAccountId,
            'label' => $this->label,
            'date' => $this->date,
            'amount' => $this->amount,
            'fee' => $this->fee,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
