<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class NewStandardInvoiceLine
{
    public function __construct(
        public string $label,
        public string $rawCurrencyUnitPrice,
        public string $unit,
        public VatRate $vatRate,
        public int|float $quantity,
        public ?int $ledgerAccountId = null,
        public ?Substance $substance = null,
        public ?int $sectionRank = null,
        public ?Discount $discount = null,
        public ?string $description = null,
        public ?ImputationDates $imputationDates = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'label' => $this->label,
            'ledger_account_id' => $this->ledgerAccountId,
            'substance' => $this->substance?->value,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate->value,
            'quantity' => $this->quantity,
            'section_rank' => $this->sectionRank,
            'discount' => $this->discount?->toArray(),
            'description' => $this->description,
            'imputation_dates' => $this->imputationDates?->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
