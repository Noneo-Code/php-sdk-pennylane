<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerAccounts;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostLedgerAccounts extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $number,
        private readonly string $label,
        private readonly ?VatRate $vatRate = null,
        private readonly ?CountryAlpha2 $countryAlpha2 = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/ledger_accounts';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'number' => $this->number,
            'label' => $this->label,
            'vat_rate' => $this->vatRate?->value,
            'country_alpha2' => $this->countryAlpha2?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
