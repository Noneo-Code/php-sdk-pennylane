<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class UpdateImportedInvoiceLine
{
    public function __construct(
        public int $id,
        public ?string $currencyAmount = null,
        public ?string $amount = null,
        public ?string $currencyTax = null,
        public ?string $tax = null,
        public ?string $label = null,
        public int|string|null $quantity = null,
        public ?int $ledgerAccountId = null,
        public ?string $rawCurrencyUnitPrice = null,
        public ?string $unit = null,
        public ?VatRate $vatRate = null,
        public ?int $productId = null,
        public ?ImputationDates $imputationDates = null,
        public ?string $ledgerEntryLineLabel = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'currency_amount' => $this->currencyAmount,
            'amount' => $this->amount,
            'currency_tax' => $this->currencyTax,
            'tax' => $this->tax,
            'label' => $this->label,
            'quantity' => $this->quantity,
            'ledger_account_id' => $this->ledgerAccountId,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate?->value,
            'product_id' => $this->productId,
            'imputation_dates' => $this->imputationDates?->toArray(),
            'ledger_entry_line' => null !== $this->ledgerEntryLineLabel ? ['label' => $this->ledgerEntryLineLabel] : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
