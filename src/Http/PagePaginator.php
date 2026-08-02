<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator as BasePagedPaginator;

/**
 * Une poignée d'endpoints Firm (`companies`, `fiscal_years`, `trial_balance`)
 * paginent par page plutôt que par curseur : `{items, total_pages,
 * current_page, total_items, per_page}`.
 */
final class PagePaginator extends BasePagedPaginator
{
    public function __construct(
        Connector $connector,
        Request $request,
        private readonly ?FilterSet $filters = null,
        ?int $perPage = null,
    ) {
        parent::__construct($connector, $request);

        $this->setPerPageLimit($perPage);
    }

    protected function applyPagination(Request $request): Request
    {
        $request = parent::applyPagination($request);

        if ($this->filters instanceof FilterSet && !$this->filters->isEmpty()) {
            $request->query()->add('filter', $this->filters->toJson());
        }

        return $request;
    }

    protected function isLastPage(Response $response): bool
    {
        $page = Page::fromArray($response->json());

        return $page->currentPage >= $page->totalPages;
    }

    /** @return mixed[] */
    protected function getPageItems(Response $response, Request $request): array
    {
        return Page::fromArray($response->json())->items;
    }

    /**
     * Le plugin de pagination type `items()` en `Response|PromiseInterface`
     * pour couvrir la pagination asynchrone, jamais utilisée par ce SDK.
     * On surcharge avec un type fidèle à l'usage réel : des items décodés.
     *
     * @return iterable<int, mixed>
     */
    public function items(): iterable
    {
        foreach ($this as $response) {
            if (!$response instanceof Response) {
                continue;
            }

            foreach ($this->getPageItems($response, $response->getRequest()) as $item) {
                yield $item;
            }
        }
    }
}
