<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CommercialDocument;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;

final readonly class CommercialDocumentDto
{
    public function __construct(
        public int $id,
        public ?string $label,
        public string $documentNumber,
        public CommercialDocumentType $documentType,
        public Currency $currency,
        public string $amount,
        public string $currencyAmount,
        public string $currencyAmountBeforeTax,
        public string $exchangeRate,
        public ?string $date,
        public ?string $deadline,
        public string $currencyTax,
        public string $tax,
        public BillingLanguage $language,
        public Discount $discount,
        public ?string $publicFileUrl,
        public ?string $filename,
        public ?string $specialMention,
        public ?int $customerId,
        public ?string $customerUrl,
        public string $invoiceLineSectionsUrl,
        public string $invoiceLinesUrl,
        public ?int $quoteId,
        public ?string $quoteUrl,
        public string $pdfInvoiceFreeText,
        public string $pdfInvoiceSubject,
        public ?string $pdfDescription,
        public ?int $commercialDocumentTemplateId,
        public string $appendicesUrl,
        public string $externalReference,
        public ?string $archivedAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $documentNumber = $body['document_number'] ?? null;
        $documentType = $body['document_type'] ?? null;
        $currency = $body['currency'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $exchangeRate = $body['exchange_rate'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $tax = $body['tax'] ?? null;
        $language = $body['language'] ?? null;
        $discount = $body['discount'] ?? null;
        $customer = $body['customer'] ?? null;
        $invoiceLineSections = $body['invoice_line_sections'] ?? null;
        $invoiceLines = $body['invoice_lines'] ?? null;
        $quote = $body['quote'] ?? null;
        $pdfInvoiceFreeText = $body['pdf_invoice_free_text'] ?? null;
        $pdfInvoiceSubject = $body['pdf_invoice_subject'] ?? null;
        $commercialDocumentTemplate = $body['commercial_document_template'] ?? null;
        $appendices = $body['appendices'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($documentNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "document_number" textuel.');
        }

        if (!is_string($documentType)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "document_type" textuel.');
        }

        if (!is_string($currency)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "currency" textuelle.');
        }

        if (!is_string($amount) || !is_string($currencyAmount) || !is_string($currencyAmountBeforeTax) || !is_string($exchangeRate) || !is_string($currencyTax) || !is_string($tax)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir les montants "amount", "currency_amount", "currency_amount_before_tax", "exchange_rate", "currency_tax" et "tax" sous forme de texte.');
        }

        if (!is_string($language)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "language" textuelle.');
        }

        if (!is_array($discount)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "discount".');
        }

        if (null !== $customer && !is_array($customer)) {
            throw new \InvalidArgumentException('Le "customer" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_array($invoiceLineSections) || !is_string($invoiceLineSections['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "invoice_line_sections" avec une "url" textuelle.');
        }

        if (!is_array($invoiceLines) || !is_string($invoiceLines['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "invoice_lines" avec une "url" textuelle.');
        }

        if (null !== $quote && !is_array($quote)) {
            throw new \InvalidArgumentException('Le "quote" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_string($pdfInvoiceFreeText) || !is_string($pdfInvoiceSubject)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "pdf_invoice_free_text" et "pdf_invoice_subject" sous forme de texte.');
        }

        if (null !== $commercialDocumentTemplate && !is_array($commercialDocumentTemplate)) {
            throw new \InvalidArgumentException('Le "commercial_document_template" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_array($appendices) || !is_string($appendices['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "appendices" avec une "url" textuelle.');
        }

        if (!is_string($externalReference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "external_reference" textuelle.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            label: is_string($body['label'] ?? null) ? $body['label'] : null,
            documentNumber: $documentNumber,
            documentType: CommercialDocumentType::from($documentType),
            currency: Currency::from($currency),
            amount: $amount,
            currencyAmount: $currencyAmount,
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            exchangeRate: $exchangeRate,
            date: is_string($body['date'] ?? null) ? $body['date'] : null,
            deadline: is_string($body['deadline'] ?? null) ? $body['deadline'] : null,
            currencyTax: $currencyTax,
            tax: $tax,
            language: BillingLanguage::from($language),
            discount: Discount::fromArray($discount),
            publicFileUrl: is_string($body['public_file_url'] ?? null) ? $body['public_file_url'] : null,
            filename: is_string($body['filename'] ?? null) ? $body['filename'] : null,
            specialMention: is_string($body['special_mention'] ?? null) ? $body['special_mention'] : null,
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
            invoiceLineSectionsUrl: $invoiceLineSections['url'],
            invoiceLinesUrl: $invoiceLines['url'],
            quoteId: is_array($quote) && is_int($quote['id'] ?? null) ? $quote['id'] : null,
            quoteUrl: is_array($quote) && is_string($quote['url'] ?? null) ? $quote['url'] : null,
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: is_string($body['pdf_description'] ?? null) ? $body['pdf_description'] : null,
            commercialDocumentTemplateId: is_array($commercialDocumentTemplate) && is_int($commercialDocumentTemplate['id'] ?? null) ? $commercialDocumentTemplate['id'] : null,
            appendicesUrl: $appendices['url'],
            externalReference: $externalReference,
            archivedAt: is_string($body['archived_at'] ?? null) ? $body['archived_at'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
