<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\TrialBalance\TrialBalanceLineDto;
use Noneo\Sdk\Pennylane\Http\Page;
use Noneo\Sdk\Pennylane\Http\PagePaginator;
use Noneo\Sdk\Pennylane\Requests\Firm\TrialBalance\GetTrialBalance;
use Saloon\Http\BaseResource;

final class TrialBalance extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        string $periodStart,
        string $periodEnd,
        ?bool $isAuxiliary = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Page {
        $request = new GetTrialBalance($periodStart, $periodEnd, $isAuxiliary, $page, $perPage);
        $rawPage = Page::fromArray($this->connector->send($request)->json());

        return new Page(
            array_map(static fn (mixed $item): TrialBalanceLineDto => TrialBalanceLineDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->totalPages,
            $rawPage->currentPage,
            $rawPage->totalItems,
            $rawPage->perPage,
        );
    }

    /** @return \Generator<int, TrialBalanceLineDto> */
    public function paginate(string $periodStart, string $periodEnd, ?bool $isAuxiliary = null, ?int $perPage = null): \Generator
    {
        $paginator = new PagePaginator($this->connector, new GetTrialBalance($periodStart, $periodEnd, $isAuxiliary), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield TrialBalanceLineDto::fromArray(self::asArray($item));
        }
    }
}
