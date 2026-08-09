<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Quote;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteDto;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteStatus;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class QuoteDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getQuote')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'Quote label',
            'quote_number' => 'PLERYGTXYOIMZZ',
            'currency' => 'EUR',
            'amount' => '230.32',
            'currency_amount' => '230.32',
            'currency_amount_before_tax' => '196.32',
            'exchange_rate' => '1.0',
            'date' => '2020-01-01',
            'deadline' => '2020-12-31',
            'currency_tax' => '34.0',
            'tax' => '34.0',
            'language' => 'fr_FR',
            'status' => 'pending',
            'discount' => ['type' => 'absolute', 'value' => '100.00'],
            'public_file_url' => 'https://app.pennylane.com/public/quote/pdf?encrypted_id=bzjoVJe...3D%3D',
            'filename' => 'my_file.pdf',
            'special_mention' => 'Additional details',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'invoice_line_sections' => ['url' => 'https://app.pennylane.com/api/external/v2/quotes/42/invoice_line_sections'],
            'invoice_lines' => ['url' => 'https://app.pennylane.com/api/external/v2/quotes/42/invoice_lines'],
            'linked_invoices' => ['url' => 'https://app.pennylane.com/api/external/v2/customer_invoices?filter=...'],
            'pdf_invoice_free_text' => 'Quote details',
            'pdf_invoice_subject' => 'Quote subject',
            'pdf_description' => 'Quote description',
            'quote_template' => ['id' => 9],
            'appendices' => ['url' => 'https://app.pennylane.com/api/external/v2/quotes/42/appendices'],
            'external_reference' => 'FR123',
            'archived_at' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $quote = QuoteDto::fromArray(self::documentedPayload());

        self::assertSame(42, $quote->id);
        self::assertSame('Quote label', $quote->label);
        self::assertSame('PLERYGTXYOIMZZ', $quote->quoteNumber);
        self::assertSame(Currency::Eur, $quote->currency);
        self::assertSame('230.32', $quote->amount);
        self::assertSame(BillingLanguage::FrFr, $quote->language);
        self::assertSame(QuoteStatus::Pending, $quote->status);
        self::assertInstanceOf(Discount::class, $quote->discount);
        self::assertSame(42, $quote->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $quote->customerUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/quotes/42/invoice_line_sections', $quote->invoiceLineSectionsUrl);
        self::assertSame(9, $quote->quoteTemplateId);
        self::assertSame('FR123', $quote->externalReference);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['label'] = null;
        $payload['date'] = null;
        $payload['deadline'] = null;
        $payload['public_file_url'] = null;
        $payload['filename'] = null;
        $payload['special_mention'] = null;
        $payload['customer'] = null;
        $payload['pdf_description'] = null;
        $payload['quote_template'] = null;
        $payload['archived_at'] = null;

        $quote = QuoteDto::fromArray($payload);

        self::assertNull($quote->label);
        self::assertNull($quote->date);
        self::assertNull($quote->deadline);
        self::assertNull($quote->customerId);
        self::assertNull($quote->customerUrl);
        self::assertNull($quote->quoteTemplateId);
        self::assertNull($quote->archivedAt);
    }
}
