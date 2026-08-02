<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\CommercialDocument\CommercialDocumentDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\PostCommercialDocumentAppendices;
use Noneo\Sdk\Pennylane\Tests\Dto\CommercialDocument\CommercialDocumentDtoTest;
use Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\InvoiceLineDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CommercialDocumentsTest extends TestCase
{
    public function testListReturnsACursorPageOfCommercialDocumentDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [CommercialDocumentDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->commercialDocuments()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(CommercialDocumentDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateYieldsCommercialDocumentDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [CommercialDocumentDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $documents = iterator_to_array($pennylane->commercialDocuments()->paginate());

        self::assertCount(1, $documents);
        self::assertInstanceOf(CommercialDocumentDto::class, $documents[0]);
    }

    public function testGetReturnsACommercialDocumentDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make(CommercialDocumentDtoTest::documentedPayload())]));

        $document = $pennylane->commercialDocuments()->get(42);

        self::assertInstanceOf(CommercialDocumentDto::class, $document);
        self::assertSame(42, $document->id);
    }

    /** @return array<string, mixed> */
    private static function invoiceLineSectionPayload(): array
    {
        return [
            'id' => 444,
            'title' => 'Section #1',
            'description' => 'Lorem ipsum dolor sit amet',
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    /** @return array<string, mixed> */
    private static function appendixPayload(): array
    {
        return [
            'id' => 1,
            'url' => 'https://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/Invoice42.pdf',
            'filename' => 'Invoice42.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testInvoiceLineSectionsReturnsACursorPageOfInvoiceLineSectionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [self::invoiceLineSectionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->commercialDocuments()->invoiceLineSections(42);

        self::assertInstanceOf(InvoiceLineSectionDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLineSectionsYieldsInvoiceLineSectionDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [self::invoiceLineSectionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $sections = iterator_to_array($pennylane->commercialDocuments()->paginateInvoiceLineSections(42));

        self::assertCount(1, $sections);
        self::assertInstanceOf(InvoiceLineSectionDto::class, $sections[0]);
    }

    public function testInvoiceLinesReturnsACursorPageOfInvoiceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [InvoiceLineDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->commercialDocuments()->invoiceLines(42);

        self::assertInstanceOf(InvoiceLineDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLinesYieldsInvoiceLineDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [InvoiceLineDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $lines = iterator_to_array($pennylane->commercialDocuments()->paginateInvoiceLines(42));

        self::assertCount(1, $lines);
        self::assertInstanceOf(InvoiceLineDto::class, $lines[0]);
    }

    public function testAppendicesReturnsACursorPageOfAppendixDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [self::appendixPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->commercialDocuments()->appendices(42);

        self::assertInstanceOf(AppendixDto::class, $page->items[0]);
    }

    public function testPaginateAppendicesYieldsAppendixDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [self::appendixPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $appendices = iterator_to_array($pennylane->commercialDocuments()->paginateAppendices(42));

        self::assertCount(1, $appendices);
        self::assertInstanceOf(AppendixDto::class, $appendices[0]);
    }

    public function testUploadAppendixReturnsAnAppendixDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(self::appendixPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $appendix = $pennylane->commercialDocuments()->uploadAppendix(42, '%PDF-1.4 fake content', 'appendix.pdf');

        self::assertInstanceOf(AppendixDto::class, $appendix);
        $mockClient->assertSent(PostCommercialDocumentAppendices::class);
    }
}
