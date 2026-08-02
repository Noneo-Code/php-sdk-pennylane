<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceStatusTransition;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\MatchedTransactionDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\NewSupplierInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentStatusUpdate;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceDisputeReason;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceRefusalReason;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\CreateSupplierInvoiceEInvoiceImport;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoice;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoiceCategories;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoiceLines;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoiceMatchedTransactions;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoicePayments;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoices;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\ImportSupplierInvoice;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PostSupplierInvoiceLinkedPurchaseRequests;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PutSupplierInvoice;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PutSupplierInvoiceCategories;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PutSupplierInvoiceEInvoiceStatus;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\UpdateSupplierInvoicePaymentStatus;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\ValidateAccountingSupplierInvoice;
use Saloon\Http\BaseResource;

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

    public function invoiceLines(int $supplierInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetSupplierInvoiceLines($supplierInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineDto => InvoiceLineDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineDto> */
    public function paginateInvoiceLines(int $supplierInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSupplierInvoiceLines($supplierInvoiceId, sort: $sort), sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineDto::fromArray(self::asArray($item));
        }
    }

    public function categories(int $supplierInvoiceId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetSupplierInvoiceCategories($supplierInvoiceId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $supplierInvoiceId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSupplierInvoiceCategories($supplierInvoiceId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    /**
     * @param list<CategoryAllocation> $allocations
     *
     * @return CategoryDto[]
     */
    public function categorize(int $supplierInvoiceId, array $allocations): array
    {
        $response = $this->connector->send(new PutSupplierInvoiceCategories($supplierInvoiceId, $allocations));

        $categories = [];
        foreach (self::asArray($response->json()) as $item) {
            $categories[] = CategoryDto::fromArray(self::asArray($item));
        }

        return $categories;
    }

    public function payments(int $supplierInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetSupplierInvoicePayments($supplierInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): PaymentDto => PaymentDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, PaymentDto> */
    public function paginatePayments(int $supplierInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSupplierInvoicePayments($supplierInvoiceId, sort: $sort), sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield PaymentDto::fromArray(self::asArray($item));
        }
    }

    public function matchedTransactions(int $supplierInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetSupplierInvoiceMatchedTransactions($supplierInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): MatchedTransactionDto => MatchedTransactionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, MatchedTransactionDto> */
    public function paginateMatchedTransactions(int $supplierInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSupplierInvoiceMatchedTransactions($supplierInvoiceId, sort: $sort), sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield MatchedTransactionDto::fromArray(self::asArray($item));
        }
    }

    /** @param list<NewSupplierInvoiceLine> $invoiceLines */
    public function import(
        int $fileAttachmentId,
        int $supplierId,
        string $date,
        string $deadline,
        string $currencyAmountBeforeTax,
        string $currencyAmount,
        string $currencyTax,
        array $invoiceLines,
        bool $importAsIncomplete = false,
        ?string $invoiceNumber = null,
        ?Currency $currency = null,
        ?string $amount = null,
        ?string $tax = null,
        ?string $label = null,
        ?NewTransactionReference $transactionReference = null,
        ?string $externalReference = null,
    ): SupplierInvoiceDto {
        $request = new ImportSupplierInvoice(
            fileAttachmentId: $fileAttachmentId,
            supplierId: $supplierId,
            date: $date,
            deadline: $deadline,
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            currencyAmount: $currencyAmount,
            currencyTax: $currencyTax,
            invoiceLines: $invoiceLines,
            importAsIncomplete: $importAsIncomplete,
            invoiceNumber: $invoiceNumber,
            currency: $currency,
            amount: $amount,
            tax: $tax,
            label: $label,
            transactionReference: $transactionReference,
            externalReference: $externalReference,
        );

        return SupplierInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    /** @param resource|string $fileContents */
    public function importEInvoice(mixed $fileContents, string $filename, ?EInvoiceImportOptions $invoiceOptions = null): EInvoiceImportDto
    {
        $request = new CreateSupplierInvoiceEInvoiceImport($fileContents, $filename, $invoiceOptions);

        return EInvoiceImportDto::fromArray($this->connector->send($request)->json());
    }

    public function update(
        int $id,
        ?int $supplierId = null,
        ?string $date = null,
        ?string $deadline = null,
        ?string $invoiceNumber = null,
        ?string $label = null,
        ?Currency $currency = null,
        ?string $currencyAmountBeforeTax = null,
        ?string $currencyAmount = null,
        ?string $amount = null,
        ?string $currencyTax = null,
        ?string $tax = null,
        NewTransactionReference|Clear|null $transactionReference = null,
        ?SupplierInvoiceLinesPatch $invoiceLines = null,
        ?string $externalReference = null,
    ): SupplierInvoiceDto {
        $request = new PutSupplierInvoice(
            id: $id,
            supplierId: $supplierId,
            date: $date,
            deadline: $deadline,
            invoiceNumber: $invoiceNumber,
            label: $label,
            currency: $currency,
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            currencyAmount: $currencyAmount,
            amount: $amount,
            currencyTax: $currencyTax,
            tax: $tax,
            transactionReference: $transactionReference,
            invoiceLines: $invoiceLines,
            externalReference: $externalReference,
        );

        return SupplierInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    public function linkPurchaseRequest(int $supplierInvoiceId, int $purchaseRequestId): void
    {
        $this->connector->send(new PostSupplierInvoiceLinkedPurchaseRequests($supplierInvoiceId, $purchaseRequestId));
    }

    public function updatePaymentStatus(int $id, PaymentStatusUpdate $paymentStatus): void
    {
        $this->connector->send(new UpdateSupplierInvoicePaymentStatus($id, $paymentStatus));
    }

    public function validateAccounting(int $id): SupplierInvoiceDto
    {
        return SupplierInvoiceDto::fromArray($this->connector->send(new ValidateAccountingSupplierInvoice($id))->json());
    }

    public function disputeEInvoice(int $id, SupplierEInvoiceDisputeReason $reason): SupplierInvoiceDto
    {
        $request = new PutSupplierInvoiceEInvoiceStatus($id, EInvoiceStatusTransition::Disputed, $reason);

        return SupplierInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    public function refuseEInvoice(int $id, SupplierEInvoiceRefusalReason $reason): SupplierInvoiceDto
    {
        $request = new PutSupplierInvoiceEInvoiceStatus($id, EInvoiceStatusTransition::Refused, $reason);

        return SupplierInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    public function approveEInvoice(int $id): SupplierInvoiceDto
    {
        $request = new PutSupplierInvoiceEInvoiceStatus($id, EInvoiceStatusTransition::Approved);

        return SupplierInvoiceDto::fromArray($this->connector->send($request)->json());
    }
}
