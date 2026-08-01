<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\CursorPaginator as BaseCursorPaginator;

final class CursorPaginator extends BaseCursorPaginator
{
    public function __construct(
        Connector $connector,
        Request $request,
        private readonly ?FilterSet $filters = null,
        private readonly ?Sort $sort = null,
    ) {
        parent::__construct($connector, $request);
    }

    /**
     * Pennylane exige que `filter` et `sort` soient renvoyés à chaque page :
     * sans ça, les pages suivantes reviennent silencieusement non filtrées.
     */
    protected function applyPagination(Request $request): Request
    {
        if ($this->currentResponse instanceof Response) {
            $request->query()->add('cursor', $this->getNextCursor($this->currentResponse));
        }

        if (isset($this->perPageLimit)) {
            $request->query()->add('limit', $this->perPageLimit);
        }

        if ($this->filters instanceof FilterSet && !$this->filters->isEmpty()) {
            $request->query()->add('filter', $this->filters->toJson());
        }

        if ($this->sort instanceof Sort) {
            $request->query()->add('sort', (string) $this->sort);
        }

        return $request;
    }

    protected function isLastPage(Response $response): bool
    {
        return !CursorPage::fromArray($response->json())->hasMore;
    }

    /** @return mixed[] */
    protected function getPageItems(Response $response, Request $request): array
    {
        return CursorPage::fromArray($response->json())->items;
    }

    protected function getNextCursor(Response $response): string
    {
        return CursorPage::fromArray($response->json())->nextCursor ?? '';
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
