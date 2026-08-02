<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntry;

final readonly class UpdateLedgerEntryLine
{
    public function __construct(
        public int $id,
        public ?string $debit = null,
        public ?string $credit = null,
        public ?int $ledgerAccountId = null,
        public ?string $label = null,
    ) {
    }

    /** @return array{id: int, debit?: string, credit?: string, ledger_account_id?: int, label?: string} */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'ledger_account_id' => $this->ledgerAccountId,
            'label' => $this->label,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
