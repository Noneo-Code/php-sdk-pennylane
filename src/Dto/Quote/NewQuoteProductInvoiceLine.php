<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Quote;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class NewQuoteProductInvoiceLine
{
    public function __construct(
        public int $productId,
        public int|string $quantity,
        public ?string $label = null,
        public ?int $ledgerAccountId = null,
        public ?string $rawCurrencyUnitPrice = null,
        public ?string $unit = null,
        public ?VatRate $vatRate = null,
        public ?string $description = null,
        public ?int $sectionRank = null,
        public ?Discount $discount = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'label' => $this->label,
            'ledger_account_id' => $this->ledgerAccountId,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate?->value,
            'description' => $this->description,
            'section_rank' => $this->sectionRank,
            'discount' => $this->discount?->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
