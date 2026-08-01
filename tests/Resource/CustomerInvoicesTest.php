<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceTemplateDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomHeaderFieldDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\MatchedTransactionDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\PaymentDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\DeleteCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\ImportCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\MarkAsPaidCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PostCustomerInvoiceAppendices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PostCustomerInvoices;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PutCustomerInvoiceCategories;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\UpdateCustomerInvoice;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\UpdateImportedCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CustomerInvoicesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedInvoicePayload(): array
    {
        return \Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\CustomerInvoiceDtoTest::documentedPayload();
    }

    public function testListReturnsACursorPageOfCustomerInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoicePayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(CustomerInvoiceDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateYieldsCustomerInvoiceDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoicePayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $invoices = iterator_to_array($pennylane->customerInvoices()->paginate());

        self::assertCount(1, $invoices);
        self::assertInstanceOf(CustomerInvoiceDto::class, $invoices[0]);
    }

    public function testGetReturnsACustomerInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedInvoicePayload())]));

        $invoice = $pennylane->customerInvoices()->get(42);

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
        self::assertSame(42, $invoice->id);
    }

    public function testCreateReturnsACustomerInvoiceDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedInvoicePayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->customerInvoices()->create(
            date: '2023-08-30',
            deadline: '2023-09-30',
            customerId: 42,
            invoiceLines: [new NewProductInvoiceLine(productId: 3049, quantity: 12)],
            draft: true,
        );

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
        $mockClient->assertSent(PostCustomerInvoices::class);
    }

    public function testImportReturnsACustomerInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedInvoicePayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->customerInvoices()->import(
            fileAttachmentId: 42,
            date: '2023-08-30',
            deadline: '2023-09-30',
            customerId: 42,
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            invoiceLines: [],
        );

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
        $mockClient->assertSent(ImportCustomerInvoices::class);
    }

    public function testUpdateReturnsACustomerInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->customerInvoices()->update(42, label: 'Renamed invoice');

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
        $mockClient->assertSent(UpdateCustomerInvoice::class);
    }

    public function testUpdateImportedReturnsACustomerInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->customerInvoices()->updateImported(42, invoiceNumber: 'F20230001');

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
        $mockClient->assertSent(UpdateImportedCustomerInvoice::class);
    }

    public function testMarkAsPaidSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->customerInvoices()->markAsPaid(42);

        $mockClient->assertSent(MarkAsPaidCustomerInvoice::class);
    }

    public function testFinalizeReturnsACustomerInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make($this->documentedInvoicePayload())]));

        $invoice = $pennylane->customerInvoices()->finalize(42);

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
    }

    public function testDeleteSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->customerInvoices()->delete(1234);

        $mockClient->assertSent(DeleteCustomerInvoices::class);
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

        $page = $pennylane->customerInvoices()->invoiceLineSections(42);

        self::assertInstanceOf(InvoiceLineSectionDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLineSectionsYieldsInvoiceLineSectionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoiceLineSectionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $sections = iterator_to_array($pennylane->customerInvoices()->paginateInvoiceLineSections(42));

        self::assertCount(1, $sections);
        self::assertInstanceOf(InvoiceLineSectionDto::class, $sections[0]);
    }

    /** @return array<string, mixed> */
    private function documentedInvoiceLinePayload(): array
    {
        return \Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\InvoiceLineDtoTest::documentedPayload();
    }

    public function testInvoiceLinesReturnsACursorPageOfInvoiceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoiceLinePayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->invoiceLines(42);

        self::assertInstanceOf(InvoiceLineDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLinesYieldsInvoiceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedInvoiceLinePayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $lines = iterator_to_array($pennylane->customerInvoices()->paginateInvoiceLines(42));

        self::assertCount(1, $lines);
        self::assertInstanceOf(InvoiceLineDto::class, $lines[0]);
    }

    /** @return array<string, mixed> */
    private function documentedPaymentPayload(): array
    {
        return [
            'id' => 444,
            'label' => 'Demo label',
            'currency' => 'EUR',
            'currency_amount' => '230.32',
            'status' => 'paid_out',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testPaymentsReturnsACursorPageOfPaymentDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedPaymentPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->payments(42);

        self::assertInstanceOf(PaymentDto::class, $page->items[0]);
    }

    public function testPaginatePaymentsYieldsPaymentDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedPaymentPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $payments = iterator_to_array($pennylane->customerInvoices()->paginatePayments(42));

        self::assertCount(1, $payments);
        self::assertInstanceOf(PaymentDto::class, $payments[0]);
    }

    /** @return array<string, mixed> */
    private function documentedMatchedTransactionPayload(): array
    {
        return \Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\MatchedTransactionDtoTest::documentedPayload();
    }

    public function testMatchedTransactionsReturnsACursorPageOfMatchedTransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedMatchedTransactionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->matchedTransactions(42);

        self::assertInstanceOf(MatchedTransactionDto::class, $page->items[0]);
    }

    public function testPaginateMatchedTransactionsYieldsMatchedTransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedMatchedTransactionPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $transactions = iterator_to_array($pennylane->customerInvoices()->paginateMatchedTransactions(42));

        self::assertCount(1, $transactions);
        self::assertInstanceOf(MatchedTransactionDto::class, $transactions[0]);
    }

    /** @return array<string, mixed> */
    private function documentedAppendixPayload(): array
    {
        return [
            'id' => 1,
            'url' => 'https://www.pennylane.com/rails/active_storage/blobs/redirect/xyz/Invoice42.pdf',
            'filename' => 'Invoice42.pdf',
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

        $page = $pennylane->customerInvoices()->appendices(42);

        self::assertInstanceOf(AppendixDto::class, $page->items[0]);
    }

    public function testPaginateAppendicesYieldsAppendixDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedAppendixPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $appendices = iterator_to_array($pennylane->customerInvoices()->paginateAppendices(42));

        self::assertCount(1, $appendices);
        self::assertInstanceOf(AppendixDto::class, $appendices[0]);
    }

    public function testUploadAppendixReturnsAnAppendixDtoAndSendsTheFile(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedAppendixPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $appendix = $pennylane->customerInvoices()->uploadAppendix(42, 'file-content', 'invoice.pdf');

        self::assertInstanceOf(AppendixDto::class, $appendix);
        $mockClient->assertSent(PostCustomerInvoiceAppendices::class);
    }

    /** @return array<string, mixed> */
    private function documentedCategoryPayload(): array
    {
        return [
            'id' => 421,
            'label' => 'HR - Salaries',
            'weight' => '0.25',
            'category_group' => ['id' => 229],
            'analytical_code' => 'CODE123',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testCategoriesReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedCategoryPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->categories(42);

        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
    }

    public function testPaginateCategoriesYieldsCategoryDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedCategoryPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $categories = iterator_to_array($pennylane->customerInvoices()->paginateCategories(42));

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
    }

    public function testCategorizeReturnsCategoryDtoListAndSendsTheAllocations(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([$this->documentedCategoryPayload()])]);
        $pennylane->withMockClient($mockClient);

        $categories = $pennylane->customerInvoices()->categorize(42, [new CategoryAllocation(421, '0.25')]);

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
        $mockClient->assertSent(function (PutCustomerInvoiceCategories $sent): bool {
            return [['id' => 421, 'weight' => '0.25']] === $sent->body()->all();
        });
    }

    /** @return array<string, mixed> */
    private function documentedCustomHeaderFieldPayload(): array
    {
        return [
            'id' => 444,
            'title' => 'customer_reference',
            'value' => 'REF-12345',
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testCustomHeaderFieldsReturnsACursorPageOfCustomHeaderFieldDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedCustomHeaderFieldPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->customHeaderFields(42);

        self::assertInstanceOf(CustomHeaderFieldDto::class, $page->items[0]);
    }

    public function testPaginateCustomHeaderFieldsYieldsCustomHeaderFieldDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedCustomHeaderFieldPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $fields = iterator_to_array($pennylane->customerInvoices()->paginateCustomHeaderFields(42));

        self::assertCount(1, $fields);
        self::assertInstanceOf(CustomHeaderFieldDto::class, $fields[0]);
    }

    /** @return array<string, mixed> */
    private function documentedTemplatePayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Standard Invoice Template',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testTemplatesReturnsACursorPageOfCustomerInvoiceTemplateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedTemplatePayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->customerInvoices()->templates();

        self::assertInstanceOf(CustomerInvoiceTemplateDto::class, $page->items[0]);
    }

    public function testPaginateTemplatesYieldsCustomerInvoiceTemplateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedTemplatePayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $templates = iterator_to_array($pennylane->customerInvoices()->paginateTemplates());

        self::assertCount(1, $templates);
        self::assertInstanceOf(CustomerInvoiceTemplateDto::class, $templates[0]);
    }
}
