<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\UpdateLedgerEntryLine;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\GetLedgerEntries;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\GetLedgerEntry;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\PostLedgerEntries;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\PutLedgerEntries;
use Saloon\Http\BaseResource;

final class LedgerEntries extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetLedgerEntries($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = LedgerEntryDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, LedgerEntryDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetLedgerEntries(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
        );

        foreach ($paginator->items() as $item) {
            yield LedgerEntryDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): LedgerEntryDto
    {
        return LedgerEntryDto::fromArray($this->connector->send(new GetLedgerEntry($id))->json());
    }

    /** @param list<NewLedgerEntryLine> $ledgerEntryLines */
    public function create(
        string $date,
        string $label,
        int $journalId,
        array $ledgerEntryLines,
        ?string $dueDate = null,
        ?int $fileAttachmentId = null,
        ?Currency $currency = null,
        ?string $pieceNumber = null,
    ): LedgerEntryDto {
        $request = new PostLedgerEntries(
            date: $date,
            label: $label,
            journalId: $journalId,
            ledgerEntryLines: $ledgerEntryLines,
            dueDate: $dueDate,
            fileAttachmentId: $fileAttachmentId,
            currency: $currency,
            pieceNumber: $pieceNumber,
        );

        return LedgerEntryDto::fromArray($this->connector->send($request)->json());
    }

    /**
     * @param ?list<NewLedgerEntryLine>    $createLines
     * @param ?list<UpdateLedgerEntryLine> $updateLines
     * @param ?list<int>                   $deleteLineIds
     */
    public function update(
        int $id,
        ?string $date = null,
        ?string $label = null,
        ?int $journalId = null,
        ?int $fileAttachmentId = null,
        ?Currency $currency = null,
        ?string $pieceNumber = null,
        ?array $createLines = null,
        ?array $updateLines = null,
        ?array $deleteLineIds = null,
    ): LedgerEntryDto {
        $request = new PutLedgerEntries(
            id: $id,
            date: $date,
            label: $label,
            journalId: $journalId,
            fileAttachmentId: $fileAttachmentId,
            currency: $currency,
            pieceNumber: $pieceNumber,
            createLines: $createLines,
            updateLines: $updateLines,
            deleteLineIds: $deleteLineIds,
        );

        return LedgerEntryDto::fromArray($this->connector->send($request)->json());
    }

    /** @return mixed[] */
    private static function asArray(mixed $item): array
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Un item de la réponse Pennylane doit être un objet.');
        }

        return $item;
    }
}
