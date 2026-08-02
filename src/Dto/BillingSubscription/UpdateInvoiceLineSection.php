<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

final readonly class UpdateInvoiceLineSection
{
    public function __construct(
        public int $id,
        public ?string $title = null,
        public ?string $description = null,
        public ?int $rank = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'rank' => $this->rank,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
