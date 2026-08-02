<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\MatchedTransactionDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\NewSupplierInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentStatusUpdate;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceDisputeReason;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceRefusalReason;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class SupplierInvoicesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedSupplierInvoicePayload(): array
    {
        return [
            'id' => 123,
            'label' => 'Demo label',
            'invoice_number' => 'F20230001',
            'currency' => 'EUR',
            'amount' => '230.32',
            'currency_amount' => '230.32',
            'currency_amount_before_tax' => '196.32',
            'exchange_rate' => '1.0',
            'date' => '2023-08-30',
            'deadline' => '2023-09-30',
            'currency_tax' => '34.0',
            'tax' => '34.0',
            'reconciled' => false,
            'accounting_status' => 'draft',
            'filename' => 'my_file.pdf',
            'public_file_url' => null,
            'remaining_amount_with_tax' => '20.0',
            'remaining_amount_without_tax' => '16.0',
            'ledger_entry' => ['id' => 42003],
            'supplier' => ['id' => 456, 'url' => 'https://app.pennylane.com/api/external/v2/suppliers/42'],
            'invoice_lines' => ['url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42/invoice_lines'],
            'categories' => ['url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42/categories'],
            'transaction_reference' => null,
            'payment_status' => 'to_be_processed',
            'paid' => false,
            'payments' => ['url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42/payments'],
            'matched_transactions' => ['url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42/matched_transactions'],
            'external_reference' => 'FR123',
            'import_source' => null,
            'e_invoicing' => null,
            'archived_at' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
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

    public function testListReturnsACursorPageOfSupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [$this->documentedSupplierInvoicePayload()],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->supplierInvoices()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(SupplierInvoiceDto::class, $page->items[0]);
        self::assertSame(123, $page->items[0]->id);
    }

    public function testGetReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->get(123);

        self::assertSame(123, $invoice->id);
    }

    public function testCategorizeReturnsAListOfCategoryDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([$this->documentedCategoryPayload()])]);
        $pennylane->withMockClient($mockClient);

        $categories = $pennylane->supplierInvoices()->categorize(123, [new CategoryAllocation(421, '1')]);

        self::assertCount(1, $categories);
        self::assertSame(421, $categories[0]->id);
    }

    public function testPaymentsReturnsACursorPageOfPaymentDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [[
                'id' => 444,
                'label' => 'Demo label',
                'currency' => 'EUR',
                'currency_amount' => '230.32',
                'status' => 'initiated',
                'created_at' => '2023-08-30T10:08:08.146343Z',
                'updated_at' => '2023-08-30T10:08:08.146343Z',
            ]],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->supplierInvoices()->payments(123);

        self::assertInstanceOf(PaymentDto::class, $page->items[0]);
    }

    public function testMatchedTransactionsReturnsACursorPageOfMatchedTransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [[
                'id' => 42,
                'label' => null,
                'attachment_required' => true,
                'date' => '2023-08-30',
                'outstanding_balance' => null,
                'created_at' => '2023-08-30T10:08:08.146343Z',
                'updated_at' => '2023-08-30T10:08:08.146343Z',
                'archived_at' => null,
                'currency' => 'EUR',
                'currency_amount' => '120.00',
                'amount' => '120.00',
                'currency_fee' => null,
                'fee' => null,
                'journal' => ['id' => 42],
                'bank_account' => ['id' => 53, 'url' => 'https://app.pennylane.com/api/external/v2/bank_accounts/53'],
                'pro_account_expense' => null,
                'supplier' => null,
                'categories' => [],
            ]],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->supplierInvoices()->matchedTransactions(123);

        self::assertInstanceOf(MatchedTransactionDto::class, $page->items[0]);
    }

    public function testImportReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->import(
            fileAttachmentId: 123,
            supplierId: 456,
            date: '2021-01-30',
            deadline: '2021-01-30',
            currencyAmountBeforeTax: '196.32',
            currencyAmount: '230.32',
            currencyTax: '34.0',
            invoiceLines: [new NewSupplierInvoiceLine(currencyAmount: '230.32', currencyTax: '34.0', vatRate: VatRate::Fr200)],
        );

        self::assertInstanceOf(SupplierInvoiceDto::class, $invoice);
        self::assertSame(123, $invoice->id);
    }

    public function testUpdateReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->update(123, label: 'New label');

        self::assertSame(123, $invoice->id);
    }

    public function testLinkPurchaseRequestSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->supplierInvoices()->linkPurchaseRequest(123, 42);

        $mockClient->assertSentCount(1);
    }

    public function testUpdatePaymentStatusSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->supplierInvoices()->updatePaymentStatus(123, PaymentStatusUpdate::Paid);

        $mockClient->assertSentCount(1);
    }

    public function testValidateAccountingReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->validateAccounting(123);

        self::assertSame(123, $invoice->id);
    }

    public function testDisputeEInvoiceReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->disputeEInvoice(123, SupplierEInvoiceDisputeReason::IncorrectVatRate);

        self::assertSame(123, $invoice->id);
    }

    public function testRefuseEInvoiceReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->refuseEInvoice(123, SupplierEInvoiceRefusalReason::DuplicateInvoice);

        self::assertSame(123, $invoice->id);
    }

    public function testApproveEInvoiceReturnsASupplierInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierInvoicePayload())]);
        $pennylane->withMockClient($mockClient);

        $invoice = $pennylane->supplierInvoices()->approveEInvoice(123);

        self::assertSame(123, $invoice->id);
    }
}
