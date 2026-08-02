<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Company\CompanyDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\Page;
use Noneo\Sdk\Pennylane\Http\PagePaginator;
use Noneo\Sdk\Pennylane\Requests\Firm\Companies\Companies as GetCompanies;
use Noneo\Sdk\Pennylane\Requests\Firm\Companies\Company as GetCompany;
use Saloon\Http\BaseResource;

final class Companies extends BaseResource
{
    public function list(?int $page = null, ?int $perPage = null, ?FilterSet $filters = null): Page
    {
        $response = $this->connector->send(new GetCompanies($page, $perPage, $filters));
        $rawPage = Page::fromArray($response->json());

        return new Page(
            array_map(static fn (mixed $item): CompanyDto => CompanyDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->totalPages,
            $rawPage->currentPage,
            $rawPage->totalItems,
            $rawPage->perPage,
        );
    }

    /** @return \Generator<int, CompanyDto> */
    public function paginate(?FilterSet $filters = null): \Generator
    {
        $paginator = new PagePaginator($this->connector, new GetCompanies(filters: $filters), filters: $filters);

        foreach ($paginator->items() as $item) {
            yield CompanyDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): CompanyDto
    {
        return CompanyDto::fromArray($this->connector->send(new GetCompany($id))->json());
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
