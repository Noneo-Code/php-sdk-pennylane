<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class ImportedInvoiceLinesPatch
{
    /**
     * @param list<NewImportedInvoiceLine>    $create
     * @param list<UpdateImportedInvoiceLine> $update
     * @param list<int>                       $delete
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
                ? array_map(static fn (NewImportedInvoiceLine $line): array => $line->toArray(), $this->create)
                : null,
            'update' => [] !== $this->update
                ? array_map(static fn (UpdateImportedInvoiceLine $line): array => $line->toArray(), $this->update)
                : null,
            'delete' => [] !== $this->delete
                ? array_map(static fn (int $id): array => ['id' => $id], $this->delete)
                : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
