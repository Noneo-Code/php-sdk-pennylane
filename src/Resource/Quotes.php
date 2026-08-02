<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewInvoiceLineSection;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteDto;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteInvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteStatus;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuote;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuoteAppendices;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuoteInvoiceLines;
use Noneo\Sdk\Pennylane\Requests\Quotes\GetQuoteInvoiceLineSections;
use Noneo\Sdk\Pennylane\Requests\Quotes\ListQuotes;
use Noneo\Sdk\Pennylane\Requests\Quotes\PostQuoteAppendices;
use Noneo\Sdk\Pennylane\Requests\Quotes\PostQuotes;
use Noneo\Sdk\Pennylane\Requests\Quotes\SendByEmailQuote;
use Noneo\Sdk\Pennylane\Requests\Quotes\UpdateQuote;
use Noneo\Sdk\Pennylane\Requests\Quotes\UpdateStatusQuote;
use Saloon\Http\BaseResource;

final class Quotes extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new ListQuotes($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): QuoteDto => QuoteDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, QuoteDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new ListQuotes(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield QuoteDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): QuoteDto
    {
        return QuoteDto::fromArray($this->connector->send(new GetQuote($id))->json());
    }

    /**
     * @param list<NewQuoteProductInvoiceLine|NewQuoteStandardInvoiceLine> $invoiceLines
     * @param list<NewInvoiceLineSection>                                  $invoiceLineSections
     */
    public function create(
        string $date,
        string $deadline,
        int $customerId,
        array $invoiceLines,
        ?int $quoteTemplateId = null,
        ?string $pdfInvoiceFreeText = null,
        ?string $pdfInvoiceSubject = null,
        ?string $pdfDescription = null,
        ?Currency $currency = null,
        ?string $specialMention = null,
        ?BillingLanguage $language = null,
        ?Discount $discount = null,
        array $invoiceLineSections = [],
        ?string $externalReference = null,
    ): QuoteDto {
        $request = new PostQuotes(
            date: $date,
            deadline: $deadline,
            customerId: $customerId,
            invoiceLines: $invoiceLines,
            quoteTemplateId: $quoteTemplateId,
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: $pdfDescription,
            currency: $currency,
            specialMention: $specialMention,
            language: $language,
            discount: $discount,
            invoiceLineSections: $invoiceLineSections,
            externalReference: $externalReference,
        );

        return QuoteDto::fromArray($this->connector->send($request)->json());
    }

    public function update(
        int $id,
        string|Clear|null $date = null,
        string|Clear|null $deadline = null,
        int|Clear|null $customerId = null,
        int|Clear|null $quoteTemplateId = null,
        string|Clear|null $pdfInvoiceFreeText = null,
        string|Clear|null $pdfInvoiceSubject = null,
        string|Clear|null $pdfDescription = null,
        Currency|Clear|null $currency = null,
        string|Clear|null $specialMention = null,
        Discount|Clear|null $discount = null,
        BillingLanguage|Clear|null $language = null,
        QuoteInvoiceLinesPatch|Clear|null $invoiceLines = null,
        string|Clear|null $externalReference = null,
    ): QuoteDto {
        $request = new UpdateQuote(
            id: $id,
            date: $date,
            deadline: $deadline,
            customerId: $customerId,
            quoteTemplateId: $quoteTemplateId,
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: $pdfDescription,
            currency: $currency,
            specialMention: $specialMention,
            discount: $discount,
            language: $language,
            invoiceLines: $invoiceLines,
            externalReference: $externalReference,
        );

        return QuoteDto::fromArray($this->connector->send($request)->json());
    }

    public function updateStatus(int $id, QuoteStatus $status): QuoteDto
    {
        return QuoteDto::fromArray($this->connector->send(new UpdateStatusQuote($id, $status))->json());
    }

    /** @param ?list<string> $recipients */
    public function sendByEmail(int $id, ?array $recipients = null): void
    {
        $this->connector->send(new SendByEmailQuote($id, $recipients));
    }

    public function appendices(int $quoteId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetQuoteAppendices($quoteId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): AppendixDto => AppendixDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, AppendixDto> */
    public function paginateAppendices(int $quoteId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetQuoteAppendices($quoteId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield AppendixDto::fromArray(self::asArray($item));
        }
    }

    /** @param resource|string $fileContents */
    public function uploadAppendix(int $quoteId, mixed $fileContents, string $filename): AppendixDto
    {
        $request = new PostQuoteAppendices($quoteId, $fileContents, $filename);

        return AppendixDto::fromArray($this->connector->send($request)->json());
    }

    public function invoiceLineSections(int $quoteId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetQuoteInvoiceLineSections($quoteId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineSectionDto => InvoiceLineSectionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineSectionDto> */
    public function paginateInvoiceLineSections(int $quoteId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetQuoteInvoiceLineSections($quoteId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineSectionDto::fromArray(self::asArray($item));
        }
    }

    public function invoiceLines(int $quoteId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetQuoteInvoiceLines($quoteId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): QuoteInvoiceLineDto => QuoteInvoiceLineDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, QuoteInvoiceLineDto> */
    public function paginateInvoiceLines(int $quoteId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetQuoteInvoiceLines($quoteId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield QuoteInvoiceLineDto::fromArray(self::asArray($item));
        }
    }
}
