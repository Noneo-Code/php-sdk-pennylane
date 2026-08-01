<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDto;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDueDateRule;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierPaymentMethod;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Suppliers\GetSupplier;
use Noneo\Sdk\Pennylane\Requests\Suppliers\GetSupplierCategories;
use Noneo\Sdk\Pennylane\Requests\Suppliers\GetSuppliers;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PostSupplier;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PutSupplier;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PutSupplierCategories;
use Saloon\Http\BaseResource;

final class Suppliers extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetSuppliers($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): SupplierDto => SupplierDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, SupplierDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSuppliers(filters: $filters, sort: $sort), filters: $filters, sort: $sort);

        foreach ($paginator->items() as $item) {
            yield SupplierDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): SupplierDto
    {
        return SupplierDto::fromArray($this->connector->send(new GetSupplier($id))->json());
    }

    /** @param string[]|null $emails */
    public function create(
        string $name,
        ?string $establishmentNo = null,
        ?string $regNo = null,
        ?Address $postalAddress = null,
        ?string $vatNumber = null,
        ?string $ledgerAccountNumber = null,
        ?array $emails = null,
        ?string $iban = null,
        ?SupplierPaymentMethod $supplierPaymentMethod = null,
        ?int $supplierDueDateDelay = null,
        ?SupplierDueDateRule $supplierDueDateRule = null,
        ?string $externalReference = null,
    ): SupplierDto {
        $request = new PostSupplier(
            name: $name,
            establishmentNo: $establishmentNo,
            regNo: $regNo,
            postalAddress: $postalAddress,
            vatNumber: $vatNumber,
            ledgerAccountNumber: $ledgerAccountNumber,
            emails: $emails,
            iban: $iban,
            supplierPaymentMethod: $supplierPaymentMethod,
            supplierDueDateDelay: $supplierDueDateDelay,
            supplierDueDateRule: $supplierDueDateRule,
            externalReference: $externalReference,
        );

        return SupplierDto::fromArray($this->connector->send($request)->json());
    }

    /** @param string[]|null $emails */
    public function update(
        int $id,
        ?string $name = null,
        ?string $establishmentNo = null,
        ?string $regNo = null,
        ?Address $postalAddress = null,
        ?string $vatNumber = null,
        ?array $emails = null,
        ?string $iban = null,
        ?SupplierPaymentMethod $supplierPaymentMethod = null,
        ?int $supplierDueDateDelay = null,
        ?SupplierDueDateRule $supplierDueDateRule = null,
        ?string $externalReference = null,
    ): SupplierDto {
        $request = new PutSupplier(
            id: $id,
            name: $name,
            establishmentNo: $establishmentNo,
            regNo: $regNo,
            postalAddress: $postalAddress,
            vatNumber: $vatNumber,
            emails: $emails,
            iban: $iban,
            supplierPaymentMethod: $supplierPaymentMethod,
            supplierDueDateDelay: $supplierDueDateDelay,
            supplierDueDateRule: $supplierDueDateRule,
            externalReference: $externalReference,
        );

        return SupplierDto::fromArray($this->connector->send($request)->json());
    }

    public function categories(int $supplierId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetSupplierCategories($supplierId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = CategoryDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $supplierId): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSupplierCategories($supplierId));

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    /**
     * @param list<CategoryAllocation> $allocations
     *
     * @return CategoryDto[]
     */
    public function categorize(int $supplierId, array $allocations): array
    {
        $response = $this->connector->send(new PutSupplierCategories($supplierId, $allocations));

        $categories = [];
        foreach (self::asArray($response->json()) as $item) {
            $categories[] = CategoryDto::fromArray(self::asArray($item));
        }

        return $categories;
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
