<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Transaction\TransactionDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Transactions\CreateTransaction;
use Noneo\Sdk\Pennylane\Requests\Transactions\GetTransactions;
use Noneo\Sdk\Pennylane\Requests\Transactions\UpdateTransaction;
use Saloon\Http\BaseResource;

final class Transactions extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetTransactions($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): TransactionDto => TransactionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, TransactionDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetTransactions(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield TransactionDto::fromArray(self::asArray($item));
        }
    }

    public function create(int $bankAccountId, string $label, string $date, string $amount, ?string $fee = null): TransactionDto
    {
        $request = new CreateTransaction($bankAccountId, $label, $date, $amount, $fee);

        return TransactionDto::fromArray($this->connector->send($request)->json());
    }

    public function update(int $id, ?int $customerId = null, ?int $supplierId = null): TransactionDto
    {
        $request = new UpdateTransaction($id, $customerId, $supplierId);

        return TransactionDto::fromArray($this->connector->send($request)->json());
    }
}
