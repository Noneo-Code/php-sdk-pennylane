<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\LedgerAccountDto;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\GetLedgerAccount;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\GetLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\PostLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\UpdateLedgerAccount;
use Saloon\Http\BaseResource;

/**
 * La Firm API n'accepte pas le paramètre `sort` sur cet endpoint (absent de
 * sa spec), contrairement à la Company API.
 */
final class LedgerAccounts extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(?string $cursor = null, ?int $limit = null, ?FilterSet $filters = null): CursorPage
    {
        $response = $this->connector->send(new GetLedgerAccounts($cursor, $limit, $filters));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): LedgerAccountDto => LedgerAccountDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, LedgerAccountDto> */
    public function paginate(?FilterSet $filters = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetLedgerAccounts(filters: $filters), filters: $filters, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield LedgerAccountDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): LedgerAccountDto
    {
        return LedgerAccountDto::fromArray($this->connector->send(new GetLedgerAccount($id))->json());
    }

    public function create(string $number, string $label, ?VatRate $vatRate = null, ?CountryAlpha2 $countryAlpha2 = null): LedgerAccountDto
    {
        $request = new PostLedgerAccounts($number, $label, $vatRate, $countryAlpha2);

        return LedgerAccountDto::fromArray($this->connector->send($request)->json());
    }

    public function update(int $id, ?string $label = null, ?bool $letterable = null): LedgerAccountDto
    {
        $request = new UpdateLedgerAccount($id, $label, $letterable);

        return LedgerAccountDto::fromArray($this->connector->send($request)->json());
    }
}
