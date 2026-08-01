<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImputationDates;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class NewSupplierInvoiceLine
{
    public function __construct(
        public string $currencyAmount,
        public string $currencyTax,
        public VatRate $vatRate,
        public ?string $label = null,
        public ?string $description = null,
        public ?string $amount = null,
        public ?string $tax = null,
        public ?int $ledgerAccountId = null,
        public ?ImputationDates $imputationDates = null,
        public ?string $ledgerEntryLineLabel = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'label' => $this->label,
            'description' => $this->description,
            'currency_amount' => $this->currencyAmount,
            'amount' => $this->amount,
            'currency_tax' => $this->currencyTax,
            'tax' => $this->tax,
            'ledger_account_id' => $this->ledgerAccountId,
            'vat_rate' => $this->vatRate->value,
            'imputation_dates' => $this->imputationDates?->toArray(),
            'ledger_entry_line' => null !== $this->ledgerEntryLineLabel ? ['label' => $this->ledgerEntryLineLabel] : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
