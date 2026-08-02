<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\BankAccounts;

use Noneo\Sdk\Pennylane\Dto\BankAccount\AccountType;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostBankAccount extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $name,
        private readonly ?int $bankEstablishmentId = null,
        private readonly ?string $iban = null,
        private readonly ?string $bic = null,
        private readonly ?Currency $currency = null,
        private readonly ?AccountType $accountType = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/bank_accounts';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'bank_establishment_id' => $this->bankEstablishmentId,
            'name' => $this->name,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'currency' => $this->currency?->value,
            'account_type' => $this->accountType?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
