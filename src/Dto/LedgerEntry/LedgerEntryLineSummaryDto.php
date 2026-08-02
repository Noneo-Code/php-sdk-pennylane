<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntry;

final readonly class LedgerEntryLineSummaryDto
{
    public function __construct(
        public int $id,
        public string $debit,
        public string $credit,
        public string $label,
        public LedgerAccountReferenceDto $ledgerAccount,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $debit = $body['debit'] ?? null;
        $credit = $body['credit'] ?? null;
        $label = $body['label'] ?? null;
        $ledgerAccount = $body['ledger_account'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Une "ledger_entry_line" de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($debit)) {
            throw new \InvalidArgumentException('Une "ledger_entry_line" de la réponse Pennylane doit contenir un "debit" textuel.');
        }

        if (!is_string($credit)) {
            throw new \InvalidArgumentException('Une "ledger_entry_line" de la réponse Pennylane doit contenir un "credit" textuel.');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Une "ledger_entry_line" de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_array($ledgerAccount)) {
            throw new \InvalidArgumentException('Une "ledger_entry_line" de la réponse Pennylane doit contenir un "ledger_account" objet.');
        }

        return new self(
            id: $id,
            debit: $debit,
            credit: $credit,
            label: $label,
            ledgerAccount: LedgerAccountReferenceDto::fromArray($ledgerAccount),
        );
    }
}
