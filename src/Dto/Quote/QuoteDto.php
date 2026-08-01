<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Quote;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;

final readonly class QuoteDto
{
    public function __construct(
        public int $id,
        public ?string $label,
        public string $quoteNumber,
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
        public QuoteStatus $status,
        public Discount $discount,
        public ?string $publicFileUrl,
        public ?string $filename,
        public ?string $specialMention,
        public ?int $customerId,
        public ?string $customerUrl,
        public string $invoiceLineSectionsUrl,
        public string $invoiceLinesUrl,
        public string $linkedInvoicesUrl,
        public string $pdfInvoiceFreeText,
        public string $pdfInvoiceSubject,
        public ?string $pdfDescription,
        public ?int $quoteTemplateId,
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
        $quoteNumber = $body['quote_number'] ?? null;
        $currency = $body['currency'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $exchangeRate = $body['exchange_rate'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $tax = $body['tax'] ?? null;
        $language = $body['language'] ?? null;
        $status = $body['status'] ?? null;
        $discount = $body['discount'] ?? null;
        $customer = $body['customer'] ?? null;
        $invoiceLineSections = $body['invoice_line_sections'] ?? null;
        $invoiceLines = $body['invoice_lines'] ?? null;
        $linkedInvoices = $body['linked_invoices'] ?? null;
        $pdfInvoiceFreeText = $body['pdf_invoice_free_text'] ?? null;
        $pdfInvoiceSubject = $body['pdf_invoice_subject'] ?? null;
        $quoteTemplate = $body['quote_template'] ?? null;
        $appendices = $body['appendices'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($quoteNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "quote_number" textuel.');
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

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel.');
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

        if (!is_array($linkedInvoices) || !is_string($linkedInvoices['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "linked_invoices" avec une "url" textuelle.');
        }

        if (!is_string($pdfInvoiceFreeText) || !is_string($pdfInvoiceSubject)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "pdf_invoice_free_text" et "pdf_invoice_subject" sous forme de texte.');
        }

        if (null !== $quoteTemplate && !is_array($quoteTemplate)) {
            throw new \InvalidArgumentException('Le "quote_template" de la réponse Pennylane doit être un objet ou nul.');
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
            quoteNumber: $quoteNumber,
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
            status: QuoteStatus::from($status),
            discount: Discount::fromArray($discount),
            publicFileUrl: is_string($body['public_file_url'] ?? null) ? $body['public_file_url'] : null,
            filename: is_string($body['filename'] ?? null) ? $body['filename'] : null,
            specialMention: is_string($body['special_mention'] ?? null) ? $body['special_mention'] : null,
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
            invoiceLineSectionsUrl: $invoiceLineSections['url'],
            invoiceLinesUrl: $invoiceLines['url'],
            linkedInvoicesUrl: $linkedInvoices['url'],
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: is_string($body['pdf_description'] ?? null) ? $body['pdf_description'] : null,
            quoteTemplateId: is_array($quoteTemplate) && is_int($quoteTemplate['id'] ?? null) ? $quoteTemplate['id'] : null,
            appendicesUrl: $appendices['url'],
            externalReference: $externalReference,
            archivedAt: is_string($body['archived_at'] ?? null) ? $body['archived_at'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
