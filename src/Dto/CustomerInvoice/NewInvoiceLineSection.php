<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class NewInvoiceLineSection
{
    public function __construct(
        public int $rank,
        public ?string $title = null,
        public ?string $description = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'rank' => $this->rank,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
