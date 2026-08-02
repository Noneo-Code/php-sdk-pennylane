<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\CursorPaginator as BaseCursorPaginator;

final class ChangelogPaginator extends BaseCursorPaginator
{
    /**
     * Pennylane rejette une requête portant à la fois `start_date` et
     * `cursor` (400). `start_date` ne doit donc apparaître que sur la
     * première page ; les pages suivantes naviguent uniquement par curseur.
     */
    protected function applyPagination(Request $request): Request
    {
        if ($this->currentResponse instanceof Response) {
            $request->query()->add('cursor', $this->getNextCursor($this->currentResponse));
            $request->query()->remove('start_date');
        }

        if (isset($this->perPageLimit)) {
            $request->query()->add('limit', $this->perPageLimit);
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
