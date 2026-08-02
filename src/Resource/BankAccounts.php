<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\BankAccount\AccountType;
use Noneo\Sdk\Pennylane\Dto\BankAccount\BankAccountDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\GetBankAccount;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\GetBankAccounts;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\PostBankAccount;
use Saloon\Http\BaseResource;

final class BankAccounts extends BaseResource
{
    public function list(?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $response = $this->connector->send(new GetBankAccounts($cursor, $limit, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): BankAccountDto => BankAccountDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, BankAccountDto> */
    public function paginate(?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetBankAccounts(sort: $sort), sort: $sort);

        foreach ($paginator->items() as $item) {
            yield BankAccountDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): BankAccountDto
    {
        return BankAccountDto::fromArray($this->connector->send(new GetBankAccount($id))->json());
    }

    public function create(
        string $name,
        ?int $bankEstablishmentId = null,
        ?string $iban = null,
        ?string $bic = null,
        ?Currency $currency = null,
        ?AccountType $accountType = null,
    ): BankAccountDto {
        $request = new PostBankAccount($name, $bankEstablishmentId, $iban, $bic, $currency, $accountType);

        return BankAccountDto::fromArray($this->connector->send($request)->json());
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
