<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewInvoiceLineSection;

final readonly class InvoiceLineSectionsPatch
{
    /**
     * @param list<NewInvoiceLineSection>    $create
     * @param list<UpdateInvoiceLineSection> $update
     * @param list<int>                      $delete
     */
    public function __construct(
        public array $create = [],
        public array $update = [],
        public array $delete = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'create' => [] !== $this->create
                ? array_map(static fn (NewInvoiceLineSection $section): array => $section->toArray(), $this->create)
                : null,
            'update' => [] !== $this->update
                ? array_map(static fn (UpdateInvoiceLineSection $section): array => $section->toArray(), $this->update)
                : null,
            'delete' => [] !== $this->delete
                ? array_map(static fn (int $id): array => ['id' => $id], $this->delete)
                : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
