<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceStatus;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoicingStatus;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\SchematronValidationStatus;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class CustomerInvoiceDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getCustomerInvoice')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'Invoice label',
            'invoice_number' => 'F20230001',
            'currency' => 'EUR',
            'amount' => '230.32',
            'currency_amount' => '230.32',
            'currency_amount_before_tax' => '196.32',
            'exchange_rate' => '1.0',
            'date' => '2023-08-30',
            'deadline' => '2020-09-02',
            'currency_tax' => '34.0',
            'tax' => '34.0',
            'language' => 'fr_FR',
            'paid' => false,
            'status' => 'upcoming',
            'discount' => ['type' => 'absolute', 'value' => '100.00'],
            'ledger_entry' => ['id' => 42002],
            'public_file_url' => 'https://app.pennylane.com/public/invoice/pdf?encrypted_id=bzjoVJe...3D%3D',
            'filename' => 'my_file.pdf',
            'remaining_amount_with_tax' => '20.0',
            'remaining_amount_without_tax' => '16.0',
            'draft' => false,
            'special_mention' => 'Additional details',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'invoice_line_sections' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/invoice_line_sections'],
            'invoice_lines' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/invoice_lines'],
            'custom_header_fields' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/custom_header_fields'],
            'categories' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/categories'],
            'pdf_invoice_free_text' => 'Thanks for paying this invoice',
            'pdf_invoice_subject' => 'Invoice subject',
            'pdf_description' => 'Invoice description',
            'billing_subscription' => ['id' => 17],
            'credited_invoice' => ['id' => 40, 'url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/40'],
            'customer_invoice_template' => ['id' => 9],
            'transaction_reference' => ['banking_provider' => 'bank', 'provider_field_name' => 'label', 'provider_field_value' => 'invoice_number'],
            'payments' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/payments'],
            'matched_transactions' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/matched_transactions'],
            'appendices' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42/appendices'],
            'quote' => ['id' => 42],
            'external_reference' => 'FR123',
            'e_invoicing' => ['status' => 'sent', 'reason' => null, 'flow' => ['id' => 'b1f2c3d4-0000-0000-0000-000000000000']],
            'factur_x' => true,
            'schematron_validation_status' => 'valid',
            'archived_at' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $invoice = CustomerInvoiceDto::fromArray(self::documentedPayload());

        self::assertSame(42, $invoice->id);
        self::assertSame('Invoice label', $invoice->label);
        self::assertSame('F20230001', $invoice->invoiceNumber);
        self::assertSame(Currency::Eur, $invoice->currency);
        self::assertSame('230.32', $invoice->amount);
        self::assertSame(BillingLanguage::FrFr, $invoice->language);
        self::assertFalse($invoice->paid);
        self::assertSame(CustomerInvoiceStatus::Upcoming, $invoice->status);
        self::assertInstanceOf(Discount::class, $invoice->discount);
        self::assertSame(42002, $invoice->ledgerEntryId);
        self::assertFalse($invoice->draft);
        self::assertSame(42, $invoice->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $invoice->customerUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/customer_invoices/42/invoice_line_sections', $invoice->invoiceLineSectionsUrl);
        self::assertSame(17, $invoice->billingSubscriptionId);
        self::assertSame(40, $invoice->creditedInvoiceId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customer_invoices/40', $invoice->creditedInvoiceUrl);
        self::assertSame(9, $invoice->customerInvoiceTemplateId);
        self::assertNotNull($invoice->transactionReference);
        self::assertSame('bank', $invoice->transactionReference->bankingProvider);
        self::assertSame(42, $invoice->quoteId);
        self::assertSame('FR123', $invoice->externalReference);
        self::assertSame(EInvoicingStatus::Sent, $invoice->eInvoicingStatus);
        self::assertNull($invoice->eInvoicingReason);
        self::assertSame('b1f2c3d4-0000-0000-0000-000000000000', $invoice->eInvoicingFlowId);
        self::assertTrue($invoice->facturX);
        self::assertSame(SchematronValidationStatus::Valid, $invoice->schematronValidationStatus);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['label'] = null;
        $payload['date'] = null;
        $payload['deadline'] = null;
        $payload['public_file_url'] = null;
        $payload['filename'] = null;
        $payload['remaining_amount_with_tax'] = null;
        $payload['remaining_amount_without_tax'] = null;
        $payload['special_mention'] = null;
        $payload['customer'] = null;
        $payload['pdf_description'] = null;
        $payload['billing_subscription'] = null;
        $payload['credited_invoice'] = null;
        $payload['customer_invoice_template'] = null;
        $payload['transaction_reference'] = null;
        $payload['quote'] = null;
        $payload['e_invoicing'] = null;
        $payload['schematron_validation_status'] = null;
        $payload['archived_at'] = null;

        $invoice = CustomerInvoiceDto::fromArray($payload);

        self::assertNull($invoice->label);
        self::assertNull($invoice->date);
        self::assertNull($invoice->deadline);
        self::assertNull($invoice->customerId);
        self::assertNull($invoice->customerUrl);
        self::assertNull($invoice->billingSubscriptionId);
        self::assertNull($invoice->creditedInvoiceId);
        self::assertNull($invoice->customerInvoiceTemplateId);
        self::assertNull($invoice->transactionReference);
        self::assertNull($invoice->quoteId);
        self::assertNull($invoice->eInvoicingStatus);
        self::assertNull($invoice->eInvoicingReason);
        self::assertNull($invoice->eInvoicingFlowId);
        self::assertNull($invoice->schematronValidationStatus);
        self::assertNull($invoice->archivedAt);
    }
}
