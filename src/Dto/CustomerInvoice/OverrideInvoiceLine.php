<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class OverrideInvoiceLine
{
    public function __construct(
        public string $currencyAmount,
        public string $currencyTax,
        public VatRate $vatRate,
        public int $ledgerAccountId,
        public int|float $quantity,
        public string $rawCurrencyUnitPrice,
        public ?int $productId = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'currency_amount' => $this->currencyAmount,
            'currency_tax' => $this->currencyTax,
            'vat_rate' => $this->vatRate->value,
            'ledger_account_id' => $this->ledgerAccountId,
            'quantity' => $this->quantity,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'product_id' => $this->productId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
