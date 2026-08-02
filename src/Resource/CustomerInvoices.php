<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceTemplateDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomHeaderFieldDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImportedInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\MatchedTransactionDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewImportedInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewInvoiceLineSection;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\OverrideInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\PaymentDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\CreateCustomerInvoiceEInvoiceImport;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\CreateCustomerInvoiceFromQuote;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\DeleteCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\FinalizeCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceAppendices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceCategories;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceCustomHeaderFields;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceInvoiceLines;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceInvoiceLineSections;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceMatchedTransactions;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoicePayments;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoiceTemplates;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\ImportCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\LinkCreditNote;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\MarkAsPaidCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PostCustomerInvoiceAppendices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PostCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PutCustomerInvoiceCategories;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\SendByEmailCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\SendToPaCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\UpdateCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\UpdateImportedCustomerInvoice;
use Saloon\Http\BaseResource;

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
        $paginator = new CursorPaginator(
            $this->connector,
            new GetCustomerInvoices(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
            perPage: $perPage,
        );

        foreach ($paginator->items() as $item) {
            yield CustomerInvoiceDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): CustomerInvoiceDto
    {
        return CustomerInvoiceDto::fromArray($this->connector->send(new GetCustomerInvoice($id))->json());
    }

    /**
     * @param list<NewProductInvoiceLine|NewStandardInvoiceLine> $invoiceLines
     * @param list<NewInvoiceLineSection>                        $invoiceLineSections
     */
    public function create(
        string $date,
        string $deadline,
        int $customerId,
        array $invoiceLines,
        bool $draft = false,
        ?int $customerInvoiceTemplateId = null,
        ?string $pdfInvoiceFreeText = null,
        ?string $pdfInvoiceSubject = null,
        ?string $pdfDescription = null,
        ?Currency $currency = null,
        ?string $specialMention = null,
        ?BillingLanguage $language = null,
        ?Discount $discount = null,
        array $invoiceLineSections = [],
        ?string $label = null,
        ?string $externalReference = null,
        ?NewTransactionReference $transactionReference = null,
    ): CustomerInvoiceDto {
        $request = new PostCustomerInvoices(
            date: $date,
            deadline: $deadline,
            customerId: $customerId,
            invoiceLines: $invoiceLines,
            draft: $draft,
            customerInvoiceTemplateId: $customerInvoiceTemplateId,
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: $pdfDescription,
            currency: $currency,
            specialMention: $specialMention,
            language: $language,
            discount: $discount,
            invoiceLineSections: $invoiceLineSections,
            label: $label,
            externalReference: $externalReference,
            transactionReference: $transactionReference,
        );

        return CustomerInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    /** @param list<NewImportedInvoiceLine> $invoiceLines */
    public function import(
        int $fileAttachmentId,
        string $date,
        string $deadline,
        int $customerId,
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
        bool $convertToEInvoice = false,
        ?Discount $discount = null,
        ?string $externalReference = null,
    ): CustomerInvoiceDto {
        $request = new ImportCustomerInvoices(
            fileAttachmentId: $fileAttachmentId,
            date: $date,
            deadline: $deadline,
            customerId: $customerId,
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
            convertToEInvoice: $convertToEInvoice,
            discount: $discount,
            externalReference: $externalReference,
        );

        return CustomerInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    public function createFromQuote(
        int $quoteId,
        bool $draft,
        ?string $externalReference = null,
        ?int $customerInvoiceTemplateId = null,
    ): CustomerInvoiceDto {
        $request = new CreateCustomerInvoiceFromQuote($quoteId, $draft, $externalReference, $customerInvoiceTemplateId);

        return CustomerInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    /**
     * @param resource|string            $fileContents
     * @param ?list<OverrideInvoiceLine> $overrideInvoiceLines
     */
    public function importEInvoice(
        mixed $fileContents,
        string $filename,
        ?bool $sendToPa = null,
        ?EInvoiceImportOptions $invoiceOptions = null,
        ?array $overrideInvoiceLines = null,
    ): EInvoiceImportDto {
        $request = new CreateCustomerInvoiceEInvoiceImport($fileContents, $filename, $sendToPa, $invoiceOptions, $overrideInvoiceLines);

        return EInvoiceImportDto::fromArray($this->connector->send($request)->json());
    }

    public function update(
        int $id,
        ?string $date = null,
        ?string $deadline = null,
        ?int $customerId = null,
        ?int $customerInvoiceTemplateId = null,
        string|Clear|null $pdfInvoiceFreeText = null,
        string|Clear|null $pdfInvoiceSubject = null,
        string|Clear|null $pdfDescription = null,
        ?Currency $currency = null,
        string|Clear|null $specialMention = null,
        ?Discount $discount = null,
        ?BillingLanguage $language = null,
        ?InvoiceLinesPatch $invoiceLines = null,
        string|Clear|null $label = null,
        ?string $externalReference = null,
        NewTransactionReference|Clear|null $transactionReference = null,
    ): CustomerInvoiceDto {
        $request = new UpdateCustomerInvoice(
            id: $id,
            date: $date,
            deadline: $deadline,
            customerId: $customerId,
            customerInvoiceTemplateId: $customerInvoiceTemplateId,
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: $pdfDescription,
            currency: $currency,
            specialMention: $specialMention,
            discount: $discount,
            language: $language,
            invoiceLines: $invoiceLines,
            label: $label,
            externalReference: $externalReference,
            transactionReference: $transactionReference,
        );

        return CustomerInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    public function updateImported(
        int $id,
        ?string $date = null,
        ?string $deadline = null,
        ?int $customerId = null,
        ?string $invoiceNumber = null,
        ?Currency $currency = null,
        ?string $currencyAmountBeforeTax = null,
        ?string $currencyAmount = null,
        ?string $amount = null,
        ?string $currencyTax = null,
        ?string $tax = null,
        NewTransactionReference|Clear|null $transactionReference = null,
        ?ImportedInvoiceLinesPatch $invoiceLines = null,
        ?string $externalReference = null,
    ): CustomerInvoiceDto {
        $request = new UpdateImportedCustomerInvoice(
            id: $id,
            date: $date,
            deadline: $deadline,
            customerId: $customerId,
            invoiceNumber: $invoiceNumber,
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

        return CustomerInvoiceDto::fromArray($this->connector->send($request)->json());
    }

    public function linkCreditNote(int $id, int $creditNoteId): CustomerInvoiceDto
    {
        return CustomerInvoiceDto::fromArray($this->connector->send(new LinkCreditNote($id, $creditNoteId))->json());
    }

    public function markAsPaid(int $id): void
    {
        $this->connector->send(new MarkAsPaidCustomerInvoice($id));
    }

    public function finalize(int $id): CustomerInvoiceDto
    {
        return CustomerInvoiceDto::fromArray($this->connector->send(new FinalizeCustomerInvoice($id))->json());
    }

    public function delete(int $id): void
    {
        $this->connector->send(new DeleteCustomerInvoices($id));
    }

    public function sendToPa(int $id): void
    {
        $this->connector->send(new SendToPaCustomerInvoice($id));
    }

    /** @param ?list<string> $recipients */
    public function sendByEmail(int $id, ?array $recipients = null): void
    {
        $this->connector->send(new SendByEmailCustomerInvoice($id, $recipients));
    }

    public function invoiceLineSections(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerInvoiceInvoiceLineSections($customerInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineSectionDto => InvoiceLineSectionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineSectionDto> */
    public function paginateInvoiceLineSections(int $customerInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceInvoiceLineSections($customerInvoiceId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineSectionDto::fromArray(self::asArray($item));
        }
    }

    public function invoiceLines(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerInvoiceInvoiceLines($customerInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineDto => InvoiceLineDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineDto> */
    public function paginateInvoiceLines(int $customerInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceInvoiceLines($customerInvoiceId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineDto::fromArray(self::asArray($item));
        }
    }

    public function payments(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerInvoicePayments($customerInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): PaymentDto => PaymentDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, PaymentDto> */
    public function paginatePayments(int $customerInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoicePayments($customerInvoiceId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield PaymentDto::fromArray(self::asArray($item));
        }
    }

    public function matchedTransactions(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerInvoiceMatchedTransactions($customerInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): MatchedTransactionDto => MatchedTransactionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, MatchedTransactionDto> */
    public function paginateMatchedTransactions(int $customerInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceMatchedTransactions($customerInvoiceId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield MatchedTransactionDto::fromArray(self::asArray($item));
        }
    }

    public function appendices(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetCustomerInvoiceAppendices($customerInvoiceId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): AppendixDto => AppendixDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, AppendixDto> */
    public function paginateAppendices(int $customerInvoiceId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceAppendices($customerInvoiceId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield AppendixDto::fromArray(self::asArray($item));
        }
    }

    /** @param resource|string $fileContents */
    public function uploadAppendix(int $customerInvoiceId, mixed $fileContents, string $filename): AppendixDto
    {
        $request = new PostCustomerInvoiceAppendices($customerInvoiceId, $fileContents, $filename);

        return AppendixDto::fromArray($this->connector->send($request)->json());
    }

    public function categories(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetCustomerInvoiceCategories($customerInvoiceId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $customerInvoiceId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceCategories($customerInvoiceId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    /**
     * @param list<CategoryAllocation> $allocations
     *
     * @return CategoryDto[]
     */
    public function categorize(int $customerInvoiceId, array $allocations): array
    {
        $response = $this->connector->send(new PutCustomerInvoiceCategories($customerInvoiceId, $allocations));

        return array_map(
            static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)),
            self::asArray($response->json()),
        );
    }

    public function customHeaderFields(int $customerInvoiceId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerInvoiceCustomHeaderFields($customerInvoiceId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CustomHeaderFieldDto => CustomHeaderFieldDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CustomHeaderFieldDto> */
    public function paginateCustomHeaderFields(int $customerInvoiceId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceCustomHeaderFields($customerInvoiceId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CustomHeaderFieldDto::fromArray(self::asArray($item));
        }
    }

    public function templates(?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerInvoiceTemplates($cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): CustomerInvoiceTemplateDto => CustomerInvoiceTemplateDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, CustomerInvoiceTemplateDto> */
    public function paginateTemplates(?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerInvoiceTemplates(sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CustomerInvoiceTemplateDto::fromArray(self::asArray($item));
        }
    }
}
