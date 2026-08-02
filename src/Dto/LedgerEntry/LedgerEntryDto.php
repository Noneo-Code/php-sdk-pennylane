<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;

final readonly class LedgerEntryDto
{
    use DecodesPennylanePayloads;

    /**
     * @param CategoryDto[]                $categories
     * @param ?LedgerEntryLineSummaryDto[] $ledgerEntryLines
     */
    public function __construct(
        public int $id,
        public string $label,
        public ?string $pieceNumber,
        public ?string $date,
        public ?string $dueDate,
        public ?string $invoiceNumber,
        public JournalReferenceDto $journal,
        public ?LedgerEntryStatus $status,
        public array $categories,
        public ?LedgerEntryAttachmentDto $attachment,
        public ?array $ledgerEntryLines,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $pieceNumber = $body['piece_number'] ?? null;
        $date = $body['date'] ?? null;
        $dueDate = $body['due_date'] ?? null;
        $invoiceNumber = $body['invoice_number'] ?? null;
        $journal = $body['journal'] ?? null;
        $status = $body['status'] ?? null;
        $categories = $body['categories'] ?? null;
        $attachment = $body['attachment'] ?? null;
        $ledgerEntryLines = $body['ledger_entry_lines'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (null !== $pieceNumber && !is_string($pieceNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "piece_number" textuel ou nul.');
        }

        if (null !== $date && !is_string($date)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "date" textuelle ou nulle.');
        }

        if (null !== $dueDate && !is_string($dueDate)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "due_date" textuelle ou nulle.');
        }

        if (null !== $invoiceNumber && !is_string($invoiceNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "invoice_number" textuel ou nul.');
        }

        if (!is_array($journal)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "journal" objet.');
        }

        if (null !== $status && !is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel ou nul.');
        }

        if (!is_array($categories)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "categories".');
        }

        if (null !== $attachment && !is_array($attachment)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "attachment" objet ou nul.');
        }

        if (null !== $ledgerEntryLines && !is_array($ledgerEntryLines)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "ledger_entry_lines" ou rien.');
        }

        if (null !== $createdAt && !is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel ou nul.');
        }

        if (null !== $updatedAt && !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel ou nul.');
        }

        return new self(
            id: $id,
            label: $label,
            pieceNumber: $pieceNumber,
            date: $date,
            dueDate: $dueDate,
            invoiceNumber: $invoiceNumber,
            journal: JournalReferenceDto::fromArray($journal),
            status: null !== $status ? LedgerEntryStatus::from($status) : null,
            categories: array_map(
                static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)),
                $categories,
            ),
            attachment: null !== $attachment ? LedgerEntryAttachmentDto::fromArray($attachment) : null,
            ledgerEntryLines: null !== $ledgerEntryLines
                ? array_map(
                    static fn (mixed $item): LedgerEntryLineSummaryDto => LedgerEntryLineSummaryDto::fromArray(self::asArray($item)),
                    $ledgerEntryLines,
                )
                : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
