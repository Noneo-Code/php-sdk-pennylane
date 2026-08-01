<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceLineOptions;

final readonly class EInvoiceImportOptions
{
    /** @param list<EInvoiceLineOptions> $invoiceLines */
    public function __construct(
        public ?int $supplierId = null,
        public array $invoiceLines = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'supplier_id' => $this->supplierId,
            'invoice_lines' => [] !== $this->invoiceLines
                ? array_map(static fn (EInvoiceLineOptions $line): array => $line->toArray(), $this->invoiceLines)
                : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
