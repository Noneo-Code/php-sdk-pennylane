<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Products;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostProducts extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $label,
        private readonly string $priceBeforeTax,
        private readonly VatRate $vatRate,
        private readonly ?string $description = null,
        private readonly ?string $externalReference = null,
        private readonly ?string $unit = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $reference = null,
        private readonly ?int $ledgerAccountId = null,
        private readonly ?Substance $substance = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/products';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'label' => $this->label,
            'description' => $this->description,
            'external_reference' => $this->externalReference,
            'price_before_tax' => $this->priceBeforeTax,
            'vat_rate' => $this->vatRate->value,
            'unit' => $this->unit,
            'currency' => $this->currency?->value,
            'reference' => $this->reference,
            'ledger_account_id' => $this->ledgerAccountId,
            'substance' => $this->substance?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
