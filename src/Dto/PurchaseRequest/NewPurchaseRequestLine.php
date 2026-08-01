<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PurchaseRequest;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class NewPurchaseRequestLine
{
    public function __construct(
        public string $currencyAmount,
        public string $currencyTax,
        public string $label,
        public int|float $quantity,
        public string $unitPrice,
        public string $unit,
        public VatRate $vatRate,
        public ?string $amount = null,
        public ?string $tax = null,
        public ?string $description = null,
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
            'unit_price' => $this->unitPrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate->value,
            'description' => $this->description,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
