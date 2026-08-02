<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\TrialBalance;

final readonly class TrialBalanceLineDto
{
    public function __construct(
        public string $number,
        public string $formattedNumber,
        public string $label,
        public string $debits,
        public string $credits,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $number = $body['number'] ?? null;
        $formattedNumber = $body['formatted_number'] ?? null;
        $label = $body['label'] ?? null;
        $debits = $body['debits'] ?? null;
        $credits = $body['credits'] ?? null;

        if (!is_string($number)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "number" textuel.');
        }

        if (!is_string($formattedNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "formatted_number" textuel.');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_string($debits)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "debits" textuel.');
        }

        if (!is_string($credits)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "credits" textuel.');
        }

        return new self(
            number: $number,
            formattedNumber: $formattedNumber,
            label: $label,
            debits: $debits,
            credits: $credits,
        );
    }
}
