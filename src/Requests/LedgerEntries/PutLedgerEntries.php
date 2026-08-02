<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerEntries;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\UpdateLedgerEntryLine;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutLedgerEntries extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /**
     * @param ?list<NewLedgerEntryLine>    $createLines
     * @param ?list<UpdateLedgerEntryLine> $updateLines
     * @param ?list<int>                   $deleteLineIds
     */
    public function __construct(
        private readonly int $id,
        private readonly ?string $date = null,
        private readonly ?string $label = null,
        private readonly ?int $journalId = null,
        private readonly ?int $fileAttachmentId = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $pieceNumber = null,
        private readonly ?array $createLines = null,
        private readonly ?array $updateLines = null,
        private readonly ?array $deleteLineIds = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/ledger_entries/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        $ledgerEntryLines = array_filter([
            'create' => null !== $this->createLines
                ? array_map(static fn (NewLedgerEntryLine $line): array => $line->toArray(), $this->createLines)
                : null,
            'update' => null !== $this->updateLines
                ? array_map(static fn (UpdateLedgerEntryLine $line): array => $line->toArray(), $this->updateLines)
                : null,
            'delete' => null !== $this->deleteLineIds
                ? array_map(static fn (int $lineId): array => ['id' => $lineId], $this->deleteLineIds)
                : null,
        ], static fn (mixed $value): bool => null !== $value);

        return array_filter([
            'date' => $this->date,
            'label' => $this->label,
            'journal_id' => $this->journalId,
            'file_attachment_id' => $this->fileAttachmentId,
            'currency' => $this->currency?->value,
            'piece_number' => $this->pieceNumber,
            'ledger_entry_lines' => [] !== $ledgerEntryLines ? $ledgerEntryLines : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
