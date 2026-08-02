<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearDto;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\FiscalYears\GetFiscalYears;
use Saloon\Http\BaseResource;

final class FiscalYears extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetFiscalYears($cursor, $limit, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = FiscalYearDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, FiscalYearDto> */
    public function paginate(?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetFiscalYears(sort: $sort),
            sort: $sort,
            perPage: $perPage,
        );

        foreach ($paginator->items() as $item) {
            yield FiscalYearDto::fromArray(self::asArray($item));
        }
    }
}
