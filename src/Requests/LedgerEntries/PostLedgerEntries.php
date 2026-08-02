<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerEntries;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostLedgerEntries extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param list<NewLedgerEntryLine> $ledgerEntryLines */
    public function __construct(
        private readonly string $date,
        private readonly string $label,
        private readonly int $journalId,
        private readonly array $ledgerEntryLines,
        private readonly ?string $dueDate = null,
        private readonly ?int $fileAttachmentId = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $pieceNumber = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/ledger_entries';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'date' => $this->date,
            'label' => $this->label,
            'journal_id' => $this->journalId,
            'due_date' => $this->dueDate,
            'file_attachment_id' => $this->fileAttachmentId,
            'currency' => $this->currency?->value,
            'piece_number' => $this->pieceNumber,
            'ledger_entry_lines' => array_map(
                static fn (NewLedgerEntryLine $line): array => $line->toArray(),
                $this->ledgerEntryLines,
            ),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
