<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class ImputationDates
{
    public function __construct(
        public string $startDate,
        public string $endDate,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $startDate = $body['start_date'] ?? null;
        $endDate = $body['end_date'] ?? null;

        if (!is_string($startDate)) {
            throw new \InvalidArgumentException('Les "imputation_dates" de la réponse Pennylane doivent contenir une "start_date" textuelle.');
        }

        if (!is_string($endDate)) {
            throw new \InvalidArgumentException('Les "imputation_dates" de la réponse Pennylane doivent contenir une "end_date" textuelle.');
        }

        return new self($startDate, $endDate);
    }

    /** @return array{start_date: string, end_date: string} */
    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
