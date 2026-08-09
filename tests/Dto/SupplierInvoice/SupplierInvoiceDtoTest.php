<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoicingStatus;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceAccountingStatus;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoicePaymentStatus;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class SupplierInvoiceDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getSupplierInvoice')]
    public static function documentedPayload(): array
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
            'public_file_url' => 'https://app.pennylane.com/public/invoice/pdf?encrypted_id=xxx',
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
            'import_source' => ['source' => 'dropbox', 'path' => '/ACCOUNTING INVOICES/2024/invoice.pdf'],
            'e_invoicing' => [
                'status' => 'waiting_for_validation',
                'reason' => null,
                'flow' => ['id' => '018e5b3a-9f3b-7c3e-8f3e-9f3b7c3e8f3e'],
                'source_file_url' => 'https://app.pennylane.com/rails/active_storage/blobs/redirect/abc123/invoice.xml',
            ],
            'archived_at' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testFromArrayBuildsASupplierInvoiceDtoFromTheDocumentedPayload(): void
    {
        $invoice = SupplierInvoiceDto::fromArray($this->documentedPayload());

        self::assertSame(123, $invoice->id);
        self::assertSame('Demo label', $invoice->label);
        self::assertSame('F20230001', $invoice->invoiceNumber);
        self::assertSame(Currency::Eur, $invoice->currency);
        self::assertSame('230.32', $invoice->amount);
        self::assertFalse($invoice->reconciled);
        self::assertSame(SupplierInvoiceAccountingStatus::Draft, $invoice->accountingStatus);
        self::assertSame(42003, $invoice->ledgerEntryId);
        self::assertSame(456, $invoice->supplierId);
        self::assertSame('https://app.pennylane.com/api/external/v2/suppliers/42', $invoice->supplierUrl);
        self::assertSame(SupplierInvoicePaymentStatus::ToBeProcessed, $invoice->paymentStatus);
        self::assertFalse($invoice->paid);
        self::assertSame('FR123', $invoice->externalReference);
        self::assertNotNull($invoice->importSource);
        self::assertSame('/ACCOUNTING INVOICES/2024/invoice.pdf', $invoice->importSource->path);
        self::assertSame(SupplierEInvoicingStatus::WaitingForValidation, $invoice->eInvoicingStatus);
        self::assertNull($invoice->eInvoicingReason);
        self::assertSame('018e5b3a-9f3b-7c3e-8f3e-9f3b7c3e8f3e', $invoice->eInvoicingFlowId);
        self::assertNull($invoice->archivedAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $invoice->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $invoice->updatedAt);
    }

    public function testFromArrayAllowsNullableFieldsToBeNull(): void
    {
        $payload = $this->documentedPayload();
        $payload['label'] = null;
        $payload['date'] = null;
        $payload['deadline'] = null;
        $payload['filename'] = null;
        $payload['supplier'] = null;
        $payload['import_source'] = null;
        $payload['e_invoicing'] = null;

        $invoice = SupplierInvoiceDto::fromArray($payload);

        self::assertNull($invoice->label);
        self::assertNull($invoice->date);
        self::assertNull($invoice->deadline);
        self::assertNull($invoice->filename);
        self::assertNull($invoice->supplierId);
        self::assertNull($invoice->supplierUrl);
        self::assertNull($invoice->importSource);
        self::assertNull($invoice->eInvoicingStatus);
    }

    public function testFromArrayToleratesAMissingPaidFieldForTheEInvoiceStatusResponseVariant(): void
    {
        $payload = $this->documentedPayload();
        unset($payload['paid'], $payload['import_source']);

        $invoice = SupplierInvoiceDto::fromArray($payload);

        self::assertNull($invoice->paid);
        self::assertNull($invoice->importSource);
    }
}
