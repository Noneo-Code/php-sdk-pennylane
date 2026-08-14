<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoices;
use Saloon\Http\BaseResource;

/**
 * Factures client d'une entreprise, vues du cabinet. Ressource distincte de
 * `Resource\CustomerInvoices` et volontairement en lecture seule : la spec Firm
 * v1 n'expose sur ce groupe que `getCustomerInvoices` et `getCustomerInvoice`.
 * Réutiliser la ressource Company exposerait côté cabinet la création,
 * la finalisation ou l'envoi par e-mail, qui n'y répondent pas.
 */
final class CustomerInvoices extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetCustomerInvoices($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CustomerInvoiceDto => CustomerInvoiceDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CustomerInvoiceDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoices(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CustomerInvoiceDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): CustomerInvoiceDto
    {
        return CustomerInvoiceDto::fromArray($this->connector->send(new GetCustomerInvoice($id))->json());
    }
}
