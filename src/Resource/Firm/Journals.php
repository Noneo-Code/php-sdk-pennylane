<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Journals\GetJournal;
use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
use Noneo\Sdk\Pennylane\Requests\Journals\PostJournals;
use Saloon\Http\BaseResource;

/**
 * La Firm API n'accepte pas le paramètre `sort` sur cet endpoint (absent de
 * sa spec), contrairement à la Company API — d'où l'absence de ce paramètre
 * ici, alors que la `Request` sous-jacente (partagée) le supporte toujours.
 */
final class Journals extends BaseResource
{
    public function list(?string $cursor = null, ?int $limit = null, ?FilterSet $filters = null): CursorPage
    {
        $response = $this->connector->send(new GetJournals($cursor, $limit, $filters));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): JournalDto => JournalDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, JournalDto> */
    public function paginate(?FilterSet $filters = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetJournals(filters: $filters), filters: $filters);

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
