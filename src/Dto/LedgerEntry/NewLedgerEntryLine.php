<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntry;

final readonly class NewLedgerEntryLine
{
    public function __construct(
        public string $debit,
        public string $credit,
        public int $ledgerAccountId,
        public ?string $label = null,
    ) {
    }

    /** @return array{debit: string, credit: string, ledger_account_id: int, label?: string} */
    public function toArray(): array
    {
        return array_filter([
            'debit' => $this->debit,
            'credit' => $this->credit,
            'ledger_account_id' => $this->ledgerAccountId,
            'label' => $this->label,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
