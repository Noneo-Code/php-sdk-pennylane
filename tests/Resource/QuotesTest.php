<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteDto;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteInvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteStatus;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\PostQuotes;
use Noneo\Sdk\Pennylane\Requests\Quotes\SendByEmailQuote;
use Noneo\Sdk\Pennylane\Requests\Quotes\UpdateQuote;
use Noneo\Sdk\Pennylane\Requests\Quotes\UpdateStatusQuote;
use Noneo\Sdk\Pennylane\Tests\Dto\Quote\QuoteDtoTest;
use Noneo\Sdk\Pennylane\Tests\Dto\Quote\QuoteInvoiceLineDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class QuotesTest extends TestCase
{
    public function testListReturnsACursorPageOfQuoteDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [QuoteDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->quotes()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(QuoteDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateYieldsQuoteDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [QuoteDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $quotes = iterator_to_array($pennylane->quotes()->paginate());

        self::assertCount(1, $quotes);
        self::assertInstanceOf(QuoteDto::class, $quotes[0]);
    }

    public function testGetReturnsAQuoteDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make(QuoteDtoTest::documentedPayload())]));

        $quote = $pennylane->quotes()->get(42);

        self::assertInstanceOf(QuoteDto::class, $quote);
        self::assertSame(42, $quote->id);
    }

    public function testCreateReturnsAQuoteDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(QuoteDtoTest::documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $quote = $pennylane->quotes()->create(
            date: '2020-01-01',
            deadline: '2020-12-31',
            customerId: 42,
            invoiceLines: [new NewQuoteProductInvoiceLine(productId: 3049, quantity: 12)],
        );

        self::assertInstanceOf(QuoteDto::class, $quote);
        $mockClient->assertSent(PostQuotes::class);
    }

    public function testUpdateReturnsAQuoteDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(QuoteDtoTest::documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $quote = $pennylane->quotes()->update(42, externalReference: 'FR123');

        self::assertInstanceOf(QuoteDto::class, $quote);
        $mockClient->assertSent(UpdateQuote::class);
    }

    public function testUpdateStatusReturnsAQuoteDtoAndSendsTheStatus(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(QuoteDtoTest::documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $quote = $pennylane->quotes()->updateStatus(42, QuoteStatus::Accepted);

        self::assertInstanceOf(QuoteDto::class, $quote);
        $mockClient->assertSent(function (UpdateStatusQuote $sent): bool {
            return ['status' => 'accepted'] === $sent->body()->all();
        });
    }

    public function testSendByEmailSendsTheRecipients(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->quotes()->sendByEmail(42, ['jane@example.com']);

        $mockClient->assertSent(function (SendByEmailQuote $sent): bool {
            return ['recipients' => ['jane@example.com']] === $sent->body()->all();
        });
    }

    /** @return array<string, mixed> */
    private function documentedAppendixPayload(): array
    {
        return [
            'id' => 1,
            'url' => 'https://www.pennylane.com/rails/active_storage/blobs/redirect/xyz/Quote42.pdf',
            'filename' => 'Quote42.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testAppendicesReturnsACursorPageOfAppendixDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedAppendixPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->quotes()->appendices(42);

        self::assertInstanceOf(AppendixDto::class, $page->items[0]);
    }

    public function testPaginateAppendicesYieldsAppendixDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedAppendixPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $appendices = iterator_to_array($pennylane->quotes()->paginateAppendices(42));

        self::assertCount(1, $appendices);
        self::assertInstanceOf(AppendixDto::class, $appendices[0]);
    }

    public function testUploadAppendixReturnsAnAppendixDtoAndSendsTheFile(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedAppendixPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $appendix = $pennylane->quotes()->uploadAppendix(42, 'file-content', 'quote.pdf');

        self::assertInstanceOf(AppendixDto::class, $appendix);
    }

    /** @return array<string, mixed> */
    private function documentedInvoiceLineSectionPayload(): array
    {
        return [
            'id' => 444,
            'title' => 'Section #1',
            'description' => 'Lorem ipsum',
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testInvoiceLineSectionsReturnsACursorPageOfInvoiceLineSectionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoiceLineSectionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->quotes()->invoiceLineSections(42);

        self::assertInstanceOf(InvoiceLineSectionDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLineSectionsYieldsInvoiceLineSectionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoiceLineSectionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $sections = iterator_to_array($pennylane->quotes()->paginateInvoiceLineSections(42));

        self::assertCount(1, $sections);
        self::assertInstanceOf(InvoiceLineSectionDto::class, $sections[0]);
    }

    public function testInvoiceLinesReturnsACursorPageOfQuoteInvoiceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [QuoteInvoiceLineDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->quotes()->invoiceLines(42);

        self::assertInstanceOf(QuoteInvoiceLineDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLinesYieldsQuoteInvoiceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [QuoteInvoiceLineDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $lines = iterator_to_array($pennylane->quotes()->paginateInvoiceLines(42));

        self::assertCount(1, $lines);
        self::assertInstanceOf(QuoteInvoiceLineDto::class, $lines[0]);
    }
}
