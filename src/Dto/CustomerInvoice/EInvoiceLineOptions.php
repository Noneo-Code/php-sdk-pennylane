<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class EInvoiceLineOptions
{
    public function __construct(
        public string $eInvoiceLineId,
        public ?int $ledgerAccountId = null,
        public ?int $productId = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'e_invoice_line_id' => $this->eInvoiceLineId,
            'ledger_account_id' => $this->ledgerAccountId,
            'product_id' => $this->productId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
