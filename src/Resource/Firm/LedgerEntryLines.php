<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\LedgerEntryLineDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLine;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLines;
use Noneo\Sdk\Pennylane\Requests\LedgerEntryLines\GetLedgerEntryLinesLetteredLedgerEntryLines;
use Saloon\Http\BaseResource;

final class LedgerEntryLines extends BaseResource
{
    use DecodesPennylanePayloads;

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
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetLedgerEntryLines(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
            perPage: $perPage,
        );

        foreach ($paginator->items() as $item) {
            yield LedgerEntryLineDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): LedgerEntryLineDto
    {
        return LedgerEntryLineDto::fromArray($this->connector->send(new GetLedgerEntryLine($id))->json());
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
    public function paginateLettered(int $ledgerEntryLineId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetLedgerEntryLinesLetteredLedgerEntryLines($ledgerEntryLineId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield LedgerEntryLineDto::fromArray(self::asArray($item));
        }
    }
}
