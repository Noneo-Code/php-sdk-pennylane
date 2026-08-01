<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

final readonly class ProAccountExpenseDto
{
    public function __construct(
        public ?int $employeeId,
        public ?string $employeeFirstName,
        public ?string $employeeLastName,
        public string $cardMaskedNumber,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $employee = $body['employee'] ?? null;
        $cardMaskedNumber = $body['card_masked_number'] ?? null;

        if (null !== $employee && !is_array($employee)) {
            throw new \InvalidArgumentException('L\'"employee" de la dépense pro Pennylane doit être un objet ou nul.');
        }

        if (!is_string($cardMaskedNumber)) {
            throw new \InvalidArgumentException('La dépense pro de la réponse Pennylane doit contenir un "card_masked_number" textuel.');
        }

        return new self(
            employeeId: is_array($employee) && is_int($employee['id'] ?? null) ? $employee['id'] : null,
            employeeFirstName: is_array($employee) && is_string($employee['first_name'] ?? null) ? $employee['first_name'] : null,
            employeeLastName: is_array($employee) && is_string($employee['last_name'] ?? null) ? $employee['last_name'] : null,
            cardMaskedNumber: $cardMaskedNumber,
        );
    }
}
