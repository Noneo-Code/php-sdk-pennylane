<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\LedgerAccountDto;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\GetLedgerAccount;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\GetLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\PostLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\UpdateLedgerAccount;
use Saloon\Http\BaseResource;

final class LedgerAccounts extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetLedgerAccounts($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = LedgerAccountDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, LedgerAccountDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetLedgerAccounts(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
        );

        foreach ($paginator->items() as $item) {
            yield LedgerAccountDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): LedgerAccountDto
    {
        return LedgerAccountDto::fromArray($this->connector->send(new GetLedgerAccount($id))->json());
    }

    public function create(
        string $number,
        string $label,
        ?VatRate $vatRate = null,
        ?CountryAlpha2 $countryAlpha2 = null,
    ): LedgerAccountDto {
        $request = new PostLedgerAccounts($number, $label, $vatRate, $countryAlpha2);

        return LedgerAccountDto::fromArray($this->connector->send($request)->json());
    }

    public function update(int $id, ?string $label = null, ?bool $letterable = null): LedgerAccountDto
    {
        $request = new UpdateLedgerAccount($id, $label, $letterable);

        return LedgerAccountDto::fromArray($this->connector->send($request)->json());
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
