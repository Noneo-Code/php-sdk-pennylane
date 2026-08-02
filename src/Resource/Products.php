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
use Noneo\Sdk\Pennylane\Http\Clear;
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
        string|Clear|null $label = null,
        string|Clear|null $description = null,
        string|Clear|null $externalReference = null,
        string|Clear|null $priceBeforeTax = null,
        VatRate|Clear|null $vatRate = null,
        string|Clear|null $unit = null,
        Currency|Clear|null $currency = null,
        string|Clear|null $reference = null,
        int|Clear|null $ledgerAccountId = null,
        Substance|Clear|null $substance = null,
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
