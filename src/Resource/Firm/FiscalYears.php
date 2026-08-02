<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearDto;
use Noneo\Sdk\Pennylane\Http\Page;
use Noneo\Sdk\Pennylane\Http\PagePaginator;
use Noneo\Sdk\Pennylane\Requests\Firm\FiscalYears\GetFiscalYears;
use Noneo\Sdk\Pennylane\Requests\Firm\FiscalYears\PostFiscalYears;
use Saloon\Http\BaseResource;

final class FiscalYears extends BaseResource
{
    public function list(?int $page = null, ?int $perPage = null): Page
    {
        $response = $this->connector->send(new GetFiscalYears($page, $perPage));
        $rawPage = Page::fromArray($response->json());

        return new Page(
            array_map(static fn (mixed $item): FiscalYearDto => FiscalYearDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->totalPages,
            $rawPage->currentPage,
            $rawPage->totalItems,
            $rawPage->perPage,
        );
    }

    /** @return \Generator<int, FiscalYearDto> */
    public function paginate(): \Generator
    {
        $paginator = new PagePaginator($this->connector, new GetFiscalYears());

        foreach ($paginator->items() as $item) {
            yield FiscalYearDto::fromArray(self::asArray($item));
        }
    }

    public function create(string $start, string $finish): FiscalYearDto
    {
        return FiscalYearDto::fromArray($this->connector->send(new PostFiscalYears($start, $finish))->json());
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
