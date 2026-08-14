<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoice;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoices;
use Saloon\Http\BaseResource;

/**
 * Factures fournisseur d'une entreprise, vues du cabinet. Ressource distincte
 * de `Resource\SupplierInvoices` et volontairement en lecture seule : la spec
 * Firm v1 n'expose sur ce groupe que `getSupplierInvoices` et
 * `getSupplierInvoice`. Réutiliser la ressource Company exposerait côté cabinet
 * une vingtaine d'écritures (import, validation comptable, statut de paiement…)
 * qui n'y répondent pas.
 */
final class SupplierInvoices extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetSupplierInvoices($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): SupplierInvoiceDto => SupplierInvoiceDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, SupplierInvoiceDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSupplierInvoices(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield SupplierInvoiceDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): SupplierInvoiceDto
    {
        return SupplierInvoiceDto::fromArray($this->connector->send(new GetSupplierInvoice($id))->json());
    }
}
