<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Journals\GetJournal;
use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
use Noneo\Sdk\Pennylane\Requests\Journals\PostJournals;
use Saloon\Http\BaseResource;

final class Journals extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetJournals($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = JournalDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, JournalDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetJournals(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
        );

        foreach ($paginator->items() as $item) {
            yield JournalDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): JournalDto
    {
        return JournalDto::fromArray($this->connector->send(new GetJournal($id))->json());
    }

    public function create(string $code, string $label): JournalDto
    {
        return JournalDto::fromArray($this->connector->send(new PostJournals($code, $label))->json());
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
