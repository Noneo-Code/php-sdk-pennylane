<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\Transaction\MatchedInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\Transaction\TransactionDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Transactions\CreateTransaction;
use Noneo\Sdk\Pennylane\Requests\Transactions\DeleteCustomerInvoiceMatchedTransactions;
use Noneo\Sdk\Pennylane\Requests\Transactions\DeleteSupplierInvoiceMatchedTransactions;
use Noneo\Sdk\Pennylane\Requests\Transactions\GetTransaction;
use Noneo\Sdk\Pennylane\Requests\Transactions\GetTransactionCategories;
use Noneo\Sdk\Pennylane\Requests\Transactions\GetTransactionMatchedInvoices;
use Noneo\Sdk\Pennylane\Requests\Transactions\GetTransactions;
use Noneo\Sdk\Pennylane\Requests\Transactions\PostCustomerInvoiceMatchedTransactions;
use Noneo\Sdk\Pennylane\Requests\Transactions\PostSupplierInvoiceMatchedTransactions;
use Noneo\Sdk\Pennylane\Requests\Transactions\PutTransactionCategories;
use Noneo\Sdk\Pennylane\Requests\Transactions\UpdateTransaction;
use Saloon\Http\BaseResource;

final class Transactions extends BaseResource
{
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
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetTransactions(filters: $filters, sort: $sort), filters: $filters, sort: $sort);

        foreach ($paginator->items() as $item) {
            yield TransactionDto::fromArray(self::asArray($item));
        }
    }

    public function create(int $bankAccountId, string $label, string $date, string $amount, ?string $fee = null): TransactionDto
    {
        $request = new CreateTransaction($bankAccountId, $label, $date, $amount, $fee);

        return TransactionDto::fromArray($this->connector->send($request)->json());
    }

    public function get(int $id): TransactionDto
    {
        return TransactionDto::fromArray($this->connector->send(new GetTransaction($id))->json());
    }

    public function update(int $id, ?int $customerId = null, ?int $supplierId = null): TransactionDto
    {
        $request = new UpdateTransaction($id, $customerId, $supplierId);

        return TransactionDto::fromArray($this->connector->send($request)->json());
    }

    public function categories(int $transactionId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetTransactionCategories($transactionId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $transactionId): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetTransactionCategories($transactionId));

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    /**
     * @param list<CategoryAllocation> $allocations
     *
     * @return CategoryDto[]
     */
    public function categorize(int $transactionId, array $allocations): array
    {
        $response = $this->connector->send(new PutTransactionCategories($transactionId, $allocations));

        $categories = [];
        foreach (self::asArray($response->json()) as $item) {
            $categories[] = CategoryDto::fromArray(self::asArray($item));
        }

        return $categories;
    }

    public function matchedInvoices(int $transactionId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetTransactionMatchedInvoices($transactionId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): MatchedInvoiceDto => MatchedInvoiceDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, MatchedInvoiceDto> */
    public function paginateMatchedInvoices(int $transactionId): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetTransactionMatchedInvoices($transactionId));

        foreach ($paginator->items() as $item) {
            yield MatchedInvoiceDto::fromArray(self::asArray($item));
        }
    }

    public function matchCustomerInvoice(int $customerInvoiceId, int $transactionId): void
    {
        $this->connector->send(new PostCustomerInvoiceMatchedTransactions($customerInvoiceId, $transactionId));
    }

    public function unmatchCustomerInvoice(int $customerInvoiceId, int $transactionId): void
    {
        $this->connector->send(new DeleteCustomerInvoiceMatchedTransactions($customerInvoiceId, $transactionId));
    }

    public function matchSupplierInvoice(int $supplierInvoiceId, int $transactionId): void
    {
        $this->connector->send(new PostSupplierInvoiceMatchedTransactions($supplierInvoiceId, $transactionId));
    }

    public function unmatchSupplierInvoice(int $supplierInvoiceId, int $transactionId): void
    {
        $this->connector->send(new DeleteSupplierInvoiceMatchedTransactions($supplierInvoiceId, $transactionId));
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
