<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\NewPurchaseRequestLine;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\PurchaseRequestDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\PurchaseRequests\CreatePurchaseRequestImport;
use Noneo\Sdk\Pennylane\Requests\PurchaseRequests\GetPurchaseRequest;
use Noneo\Sdk\Pennylane\Requests\PurchaseRequests\GetPurchaseRequests;
use Saloon\Http\BaseResource;

final class PurchaseRequests extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetPurchaseRequests($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): PurchaseRequestDto => PurchaseRequestDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, PurchaseRequestDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetPurchaseRequests(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield PurchaseRequestDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): PurchaseRequestDto
    {
        return PurchaseRequestDto::fromArray($this->connector->send(new GetPurchaseRequest($id))->json());
    }

    /** @param list<NewPurchaseRequestLine> $purchaseRequestLines */
    public function import(
        int $fileAttachmentId,
        string $reason,
        int $supplierId,
        string $purchaseOrderNumber,
        string $currencyAmountBeforeTax,
        string $currencyAmount,
        string $currencyTax,
        Address $deliveryAddress,
        array $purchaseRequestLines,
        ?string $estimatedDeliveryDate = null,
        ?Currency $currency = null,
        ?string $amount = null,
        ?string $tax = null,
    ): PurchaseRequestDto {
        $request = new CreatePurchaseRequestImport(
            fileAttachmentId: $fileAttachmentId,
            reason: $reason,
            supplierId: $supplierId,
            purchaseOrderNumber: $purchaseOrderNumber,
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            currencyAmount: $currencyAmount,
            currencyTax: $currencyTax,
            deliveryAddress: $deliveryAddress,
            purchaseRequestLines: $purchaseRequestLines,
            estimatedDeliveryDate: $estimatedDeliveryDate,
            currency: $currency,
            amount: $amount,
            tax: $tax,
        );

        return PurchaseRequestDto::fromArray($this->connector->send($request)->json());
    }
}
