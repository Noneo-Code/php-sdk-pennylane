<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\TrialBalance\TrialBalanceLineDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\TrialBalance\GetTrialBalance;
use Saloon\Http\BaseResource;

final class TrialBalance extends BaseResource
{
    public function list(
        string $periodStart,
        string $periodEnd,
        ?bool $isAuxiliary = null,
        ?string $cursor = null,
        ?int $limit = null,
    ): CursorPage {
        $request = new GetTrialBalance($periodStart, $periodEnd, $isAuxiliary, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = TrialBalanceLineDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, TrialBalanceLineDto> */
    public function paginate(string $periodStart, string $periodEnd, ?bool $isAuxiliary = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetTrialBalance($periodStart, $periodEnd, $isAuxiliary));

        foreach ($paginator->items() as $item) {
            yield TrialBalanceLineDto::fromArray(self::asArray($item));
        }
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
