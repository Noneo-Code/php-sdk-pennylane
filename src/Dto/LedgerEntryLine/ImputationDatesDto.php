<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntryLine;

final readonly class ImputationDatesDto
{
    public function __construct(
        public ?string $startDate,
        public ?string $endDate,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $startDate = $body['start_date'] ?? null;
        $endDate = $body['end_date'] ?? null;

        if (null !== $startDate && !is_string($startDate)) {
            throw new \InvalidArgumentException('Les "imputation_dates" de la réponse Pennylane doivent contenir une "start_date" textuelle ou nulle.');
        }

        if (null !== $endDate && !is_string($endDate)) {
            throw new \InvalidArgumentException('Les "imputation_dates" de la réponse Pennylane doivent contenir une "end_date" textuelle ou nulle.');
        }

        return new self($startDate, $endDate);
    }
}
