<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\CommercialDocument\CommercialDocumentDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\GetCommercialDocument;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\GetCommercialDocumentAppendices;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\GetCommercialDocumentInvoiceLines;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\GetCommercialDocumentInvoiceLineSections;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\ListCommercialDocuments;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\PostCommercialDocumentAppendices;
use Saloon\Http\BaseResource;

final class CommercialDocuments extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new ListCommercialDocuments($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CommercialDocumentDto => CommercialDocumentDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CommercialDocumentDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new ListCommercialDocuments(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CommercialDocumentDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): CommercialDocumentDto
    {
        return CommercialDocumentDto::fromArray($this->connector->send(new GetCommercialDocument($id))->json());
    }

    public function invoiceLineSections(int $commercialDocumentId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCommercialDocumentInvoiceLineSections($commercialDocumentId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineSectionDto => InvoiceLineSectionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineSectionDto> */
    public function paginateInvoiceLineSections(int $commercialDocumentId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCommercialDocumentInvoiceLineSections($commercialDocumentId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineSectionDto::fromArray(self::asArray($item));
        }
    }

    public function invoiceLines(int $commercialDocumentId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCommercialDocumentInvoiceLines($commercialDocumentId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineDto => InvoiceLineDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineDto> */
    public function paginateInvoiceLines(int $commercialDocumentId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCommercialDocumentInvoiceLines($commercialDocumentId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineDto::fromArray(self::asArray($item));
        }
    }

    public function appendices(int $commercialDocumentId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetCommercialDocumentAppendices($commercialDocumentId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): AppendixDto => AppendixDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, AppendixDto> */
    public function paginateAppendices(int $commercialDocumentId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCommercialDocumentAppendices($commercialDocumentId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield AppendixDto::fromArray(self::asArray($item));
        }
    }

    /** @param resource|string $fileContents */
    public function uploadAppendix(int $commercialDocumentId, mixed $fileContents, string $filename): AppendixDto
    {
        $request = new PostCommercialDocumentAppendices($commercialDocumentId, $fileContents, $filename);

        return AppendixDto::fromArray($this->connector->send($request)->json());
    }
}
