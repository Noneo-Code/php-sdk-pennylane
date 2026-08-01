<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class EInvoiceImportOptions
{
    /** @param list<EInvoiceLineOptions> $invoiceLines */
    public function __construct(
        public ?int $customerId = null,
        public array $invoiceLines = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'customer_id' => $this->customerId,
            'invoice_lines' => [] !== $this->invoiceLines
                ? array_map(static fn (EInvoiceLineOptions $line): array => $line->toArray(), $this->invoiceLines)
                : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
