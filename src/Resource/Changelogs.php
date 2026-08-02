<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogEntryDto;
use Noneo\Sdk\Pennylane\Dto\Changelog\LedgerEntryCategoryChangelogEntryDto;
use Noneo\Sdk\Pennylane\Dto\Changelog\LedgerEntryLineCategoryChangelogEntryDto;
use Noneo\Sdk\Pennylane\Http\ChangelogPaginator;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Requests\Changelogs\ChangelogRequest;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetCustomerChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetCustomerInvoicesChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetLedgerEntriesCategoryChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetLedgerEntryLineChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetLedgerEntryLinesCategoryChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetProductChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetQuoteChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetSupplierChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetSupplierInvoicesChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetTransactionChanges;
use Saloon\Http\BaseResource;

final class Changelogs extends BaseResource
{
    public function customerInvoices(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetCustomerInvoicesChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateCustomerInvoices(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetCustomerInvoicesChanges(startDate: $startDate));
    }

    public function customers(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetCustomerChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateCustomers(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetCustomerChanges(startDate: $startDate));
    }

    public function supplierInvoices(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetSupplierInvoicesChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateSupplierInvoices(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetSupplierInvoicesChanges(startDate: $startDate));
    }

    public function suppliers(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetSupplierChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateSuppliers(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetSupplierChanges(startDate: $startDate));
    }

    public function products(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetProductChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateProducts(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetProductChanges(startDate: $startDate));
    }

    public function quotes(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetQuoteChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateQuotes(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetQuoteChanges(startDate: $startDate));
    }

    public function ledgerEntryLines(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetLedgerEntryLineChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateLedgerEntryLines(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetLedgerEntryLineChanges(startDate: $startDate));
    }

    public function transactions(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetTransactionChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateTransactions(?string $startDate = null): \Generator
    {
        yield from $this->paginateUniform(new GetTransactionChanges(startDate: $startDate));
    }

    public function ledgerEntriesCategories(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        $request = new GetLedgerEntriesCategoryChanges($cursor, $limit, $startDate);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): LedgerEntryCategoryChangelogEntryDto => LedgerEntryCategoryChangelogEntryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, LedgerEntryCategoryChangelogEntryDto> */
    public function paginateLedgerEntriesCategories(?string $startDate = null): \Generator
    {
        $paginator = new ChangelogPaginator($this->connector, new GetLedgerEntriesCategoryChanges(startDate: $startDate));

        foreach ($paginator->items() as $item) {
            yield LedgerEntryCategoryChangelogEntryDto::fromArray(self::asArray($item));
        }
    }

    public function ledgerEntryLinesCategories(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        $request = new GetLedgerEntryLinesCategoryChanges($cursor, $limit, $startDate);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): LedgerEntryLineCategoryChangelogEntryDto => LedgerEntryLineCategoryChangelogEntryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, LedgerEntryLineCategoryChangelogEntryDto> */
    public function paginateLedgerEntryLinesCategories(?string $startDate = null): \Generator
    {
        $paginator = new ChangelogPaginator($this->connector, new GetLedgerEntryLinesCategoryChanges(startDate: $startDate));

        foreach ($paginator->items() as $item) {
            yield LedgerEntryLineCategoryChangelogEntryDto::fromArray(self::asArray($item));
        }
    }

    private function uniformPage(ChangelogRequest $request): CursorPage
    {
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): ChangelogEntryDto => ChangelogEntryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    private function paginateUniform(ChangelogRequest $request): \Generator
    {
        $paginator = new ChangelogPaginator($this->connector, $request);

        foreach ($paginator->items() as $item) {
            yield ChangelogEntryDto::fromArray(self::asArray($item));
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
