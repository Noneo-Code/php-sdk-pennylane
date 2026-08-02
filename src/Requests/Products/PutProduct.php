<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Products;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutProduct extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly string|Clear|null $label = null,
        private readonly string|Clear|null $description = null,
        private readonly string|Clear|null $externalReference = null,
        private readonly string|Clear|null $priceBeforeTax = null,
        private readonly VatRate|Clear|null $vatRate = null,
        private readonly string|Clear|null $unit = null,
        private readonly Currency|Clear|null $currency = null,
        private readonly string|Clear|null $reference = null,
        private readonly int|Clear|null $ledgerAccountId = null,
        private readonly Substance|Clear|null $substance = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/products/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'label' => $this->label,
            'description' => $this->description,
            'external_reference' => $this->externalReference,
            'price_before_tax' => $this->priceBeforeTax,
            'vat_rate' => $this->vatRate,
            'unit' => $this->unit,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'ledger_account_id' => $this->ledgerAccountId,
            'substance' => $this->substance,
        ]);
    }
}
