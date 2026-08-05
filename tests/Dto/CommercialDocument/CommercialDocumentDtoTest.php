<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CommercialDocument;

use Noneo\Sdk\Pennylane\Dto\CommercialDocument\CommercialDocumentDto;
use Noneo\Sdk\Pennylane\Dto\CommercialDocument\CommercialDocumentType;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\DiscountType;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class CommercialDocumentDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getCommercialDocument')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'Commercial document label',
            'document_number' => 'PLERYGTXYOIMZZ',
            'document_type' => 'proforma',
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
            'discount' => ['type' => 'absolute', 'value' => '25'],
            'public_file_url' => 'https://app.pennylane.com/.../file.pdf',
            'filename' => 'my_file.pdf',
            'special_mention' => 'Additional details',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'invoice_line_sections' => ['url' => 'https://app.pennylane.com/api/external/v2/commercial_documents/42/invoice_line_sections'],
            'invoice_lines' => ['url' => 'https://app.pennylane.com/api/external/v2/commercial_documents/42/invoice_lines'],
            'quote' => ['id' => 201, 'url' => 'https://app.pennylane.com/api/external/v2/quotes/201'],
            'pdf_invoice_free_text' => 'Document details',
            'pdf_invoice_subject' => 'Document subject',
            'pdf_description' => 'Document description',
            'commercial_document_template' => ['id' => 7],
            'appendices' => ['url' => 'https://app.pennylane.com/api/external/v2/commercial_documents/42/appendices'],
            'external_reference' => 'FR123',
            'archived_at' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $document = CommercialDocumentDto::fromArray(self::documentedPayload());

        self::assertSame(42, $document->id);
        self::assertSame('Commercial document label', $document->label);
        self::assertSame('PLERYGTXYOIMZZ', $document->documentNumber);
        self::assertSame(CommercialDocumentType::Proforma, $document->documentType);
        self::assertSame(Currency::Eur, $document->currency);
        self::assertSame('230.32', $document->amount);
        self::assertSame('196.32', $document->currencyAmountBeforeTax);
        self::assertSame('1.0', $document->exchangeRate);
        self::assertSame('2020-01-01', $document->date);
        self::assertSame('2020-12-31', $document->deadline);
        self::assertSame(BillingLanguage::FrFr, $document->language);
        self::assertSame(DiscountType::Absolute, $document->discount->type);
        self::assertSame('25', $document->discount->value);
        self::assertSame('https://app.pennylane.com/.../file.pdf', $document->publicFileUrl);
        self::assertSame('my_file.pdf', $document->filename);
        self::assertSame(42, $document->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $document->customerUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/commercial_documents/42/invoice_line_sections', $document->invoiceLineSectionsUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/commercial_documents/42/invoice_lines', $document->invoiceLinesUrl);
        self::assertSame(201, $document->quoteId);
        self::assertSame(7, $document->commercialDocumentTemplateId);
        self::assertSame('https://app.pennylane.com/api/external/v2/commercial_documents/42/appendices', $document->appendicesUrl);
        self::assertSame('FR123', $document->externalReference);
        self::assertNull($document->archivedAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $document->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $document->updatedAt);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['label'] = null;
        $payload['date'] = null;
        $payload['deadline'] = null;
        $payload['customer'] = null;
        $payload['quote'] = null;
        $payload['commercial_document_template'] = null;

        $document = CommercialDocumentDto::fromArray($payload);

        self::assertNull($document->label);
        self::assertNull($document->date);
        self::assertNull($document->deadline);
        self::assertNull($document->customerId);
        self::assertNull($document->customerUrl);
        self::assertNull($document->quoteId);
        self::assertNull($document->commercialDocumentTemplateId);
    }

    public function testItRejectsAMissingId(): void
    {
        $payload = self::documentedPayload();
        unset($payload['id']);

        $this->expectException(\InvalidArgumentException::class);

        CommercialDocumentDto::fromArray($payload);
    }
}
