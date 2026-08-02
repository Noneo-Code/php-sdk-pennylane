<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\Product\ProductDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Products\GetProduct;
use Noneo\Sdk\Pennylane\Requests\Products\GetProducts;
use Noneo\Sdk\Pennylane\Requests\Products\PostProducts;
use Noneo\Sdk\Pennylane\Requests\Products\PutProduct;
use Saloon\Http\BaseResource;

final class Products extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetProducts($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): ProductDto => ProductDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, ProductDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetProducts(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield ProductDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): ProductDto
    {
        return ProductDto::fromArray($this->connector->send(new GetProduct($id))->json());
    }

    public function create(
        string $label,
        string $priceBeforeTax,
        VatRate $vatRate,
        ?string $description = null,
        ?string $externalReference = null,
        ?string $unit = null,
        ?Currency $currency = null,
        ?string $reference = null,
        ?int $ledgerAccountId = null,
        ?Substance $substance = null,
    ): ProductDto {
        $request = new PostProducts(
            label: $label,
            priceBeforeTax: $priceBeforeTax,
            vatRate: $vatRate,
            description: $description,
            externalReference: $externalReference,
            unit: $unit,
            currency: $currency,
            reference: $reference,
            ledgerAccountId: $ledgerAccountId,
            substance: $substance,
        );

        return ProductDto::fromArray($this->connector->send($request)->json());
    }

    public function update(
        int $id,
        ?string $label = null,
        ?string $description = null,
        ?string $externalReference = null,
        ?string $priceBeforeTax = null,
        ?VatRate $vatRate = null,
        ?string $unit = null,
        ?Currency $currency = null,
        ?string $reference = null,
        ?int $ledgerAccountId = null,
        ?Substance $substance = null,
    ): ProductDto {
        $request = new PutProduct(
            id: $id,
            label: $label,
            description: $description,
            externalReference: $externalReference,
            priceBeforeTax: $priceBeforeTax,
            vatRate: $vatRate,
            unit: $unit,
            currency: $currency,
            reference: $reference,
            ledgerAccountId: $ledgerAccountId,
            substance: $substance,
        );

        return ProductDto::fromArray($this->connector->send($request)->json());
    }
}
