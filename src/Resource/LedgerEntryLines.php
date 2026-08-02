<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategorizedLedgerEntryLineDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\LedgerEntryLineDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\UnbalancedLetteringStrategy;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\DeleteLedgerEntryLinesUnletter;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLine;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLines;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLinesCategories;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLinesLetteredLedgerEntryLines;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\PostLedgerEntryLinesLetter;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\PutLedgerEntryLinesCategories;
use Saloon\Http\BaseResource;

final class LedgerEntryLines extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetLedgerEntryLines($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = LedgerEntryLineDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, LedgerEntryLineDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetLedgerEntryLines(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
        );

        foreach ($paginator->items() as $item) {
            yield LedgerEntryLineDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): LedgerEntryLineDto
    {
        return LedgerEntryLineDto::fromArray($this->connector->send(new GetLedgerEntryLine($id))->json());
    }

    public function categories(int $ledgerEntryLineId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetLedgerEntryLinesCategories($ledgerEntryLineId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = CategoryDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $ledgerEntryLineId, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetLedgerEntryLinesCategories($ledgerEntryLineId, sort: $sort));

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    /** @param list<CategoryAllocation> $allocations */
    public function categorize(int $ledgerEntryLineId, array $allocations): CategorizedLedgerEntryLineDto
    {
        $request = new PutLedgerEntryLinesCategories($ledgerEntryLineId, $allocations);
        $body = self::asArray($this->connector->send($request)->json());
        $ledgerEntryLine = $body['ledger_entry_line'] ?? null;

        if (!is_array($ledgerEntryLine)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_entry_line" objet.');
        }

        return CategorizedLedgerEntryLineDto::fromArray($ledgerEntryLine);
    }

    /**
     * Lettre les lignes fournies. Si l'une d'elles est déjà lettrée, le
     * lettrage se propage à toutes les lignes de son lettrage existant.
     *
     * @param list<int> $ledgerEntryLineIds
     *
     * @return int[] les identifiants de toutes les lignes du nouveau lettrage
     */
    public function letter(UnbalancedLetteringStrategy $unbalancedLetteringStrategy, array $ledgerEntryLineIds): array
    {
        $request = new PostLedgerEntryLinesLetter($unbalancedLetteringStrategy, $ledgerEntryLineIds);
        $items = self::asArray($this->connector->send($request)->json());

        return array_map(
            static function (mixed $item): int {
                $item = self::asArray($item);
                $id = $item['id'] ?? null;

                if (!is_int($id)) {
                    throw new \InvalidArgumentException('Une ligne lettrée de la réponse Pennylane doit contenir un "id" entier.');
                }

                return $id;
            },
            $items,
        );
    }

    /** @param list<int> $ledgerEntryLineIds */
    public function unletter(UnbalancedLetteringStrategy $unbalancedLetteringStrategy, array $ledgerEntryLineIds): void
    {
        $this->connector->send(new DeleteLedgerEntryLinesUnletter($unbalancedLetteringStrategy, $ledgerEntryLineIds));
    }

    public function lettered(int $ledgerEntryLineId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetLedgerEntryLinesLetteredLedgerEntryLines($ledgerEntryLineId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = LedgerEntryLineDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, LedgerEntryLineDto> */
    public function paginateLettered(int $ledgerEntryLineId, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetLedgerEntryLinesLetteredLedgerEntryLines($ledgerEntryLineId, sort: $sort));

        foreach ($paginator->items() as $item) {
            yield LedgerEntryLineDto::fromArray(self::asArray($item));
        }
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
