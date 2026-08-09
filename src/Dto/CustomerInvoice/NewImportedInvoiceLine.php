<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class NewImportedInvoiceLine
{
    public function __construct(
        public string $currencyAmount,
        public string $currencyTax,
        public int|string $quantity,
        public string $rawCurrencyUnitPrice,
        public string $unit,
        public VatRate $vatRate,
        public ?string $amount = null,
        public ?string $tax = null,
        public ?string $label = null,
        public ?int $ledgerAccountId = null,
        public ?Substance $substance = null,
        public ?int $productId = null,
        public ?ImputationDates $imputationDates = null,
        public ?string $ledgerEntryLineLabel = null,
        public ?Discount $discount = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'currency_amount' => $this->currencyAmount,
            'amount' => $this->amount,
            'currency_tax' => $this->currencyTax,
            'tax' => $this->tax,
            'label' => $this->label,
            'quantity' => $this->quantity,
            'ledger_account_id' => $this->ledgerAccountId,
            'substance' => $this->substance?->value,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate->value,
            'product_id' => $this->productId,
            'imputation_dates' => $this->imputationDates?->toArray(),
            'ledger_entry_line' => null !== $this->ledgerEntryLineLabel ? ['label' => $this->ledgerEntryLineLabel] : null,
            'discount' => $this->discount?->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
