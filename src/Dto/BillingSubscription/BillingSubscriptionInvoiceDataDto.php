<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;

final readonly class BillingSubscriptionInvoiceDataDto
{
    public function __construct(
        public ?string $label,
        public Currency $currency,
        public string $amount,
        public string $currencyAmount,
        public string $currencyAmountBeforeTax,
        public string $exchangeRate,
        public string $currencyTax,
        public BillingLanguage $language,
        public ?int $customerInvoiceTemplateId,
        public Discount $discount,
        public string $pdfInvoiceFreeText,
        public string $pdfInvoiceSubject,
        public ?string $pdfDescription,
        public ?string $specialMention,
        public string $invoiceLineSectionsUrl,
        public string $invoiceLinesUrl,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $currency = $body['currency'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $exchangeRate = $body['exchange_rate'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $language = $body['language'] ?? null;
        $customerInvoiceTemplate = $body['customer_invoice_template'] ?? null;
        $discount = $body['discount'] ?? null;
        $pdfInvoiceFreeText = $body['pdf_invoice_free_text'] ?? null;
        $pdfInvoiceSubject = $body['pdf_invoice_subject'] ?? null;
        $invoiceLineSections = $body['invoice_line_sections'] ?? null;
        $invoiceLines = $body['invoice_lines'] ?? null;

        if (!is_string($currency)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir une "currency" textuelle.');
        }

        if (!is_string($amount) || !is_string($currencyAmount) || !is_string($currencyAmountBeforeTax) || !is_string($exchangeRate) || !is_string($currencyTax)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir leurs montants sous forme de texte.');
        }

        if (!is_string($language)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir une "language" textuelle.');
        }

        if (null !== $customerInvoiceTemplate && (!is_array($customerInvoiceTemplate) || !is_int($customerInvoiceTemplate['id'] ?? null))) {
            throw new \InvalidArgumentException('Le "customer_invoice_template" de la réponse Pennylane doit être un objet avec un "id" entier, ou nul.');
        }

        if (!is_array($discount)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir un objet "discount".');
        }

        if (!is_string($pdfInvoiceFreeText) || !is_string($pdfInvoiceSubject)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir "pdf_invoice_free_text" et "pdf_invoice_subject" sous forme de texte.');
        }

        if (!is_array($invoiceLineSections) || !is_string($invoiceLineSections['url'] ?? null)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir des "invoice_line_sections" avec une "url" textuelle.');
        }

        if (!is_array($invoiceLines) || !is_string($invoiceLines['url'] ?? null)) {
            throw new \InvalidArgumentException('Les données de facturation de la réponse Pennylane doivent contenir des "invoice_lines" avec une "url" textuelle.');
        }

        return new self(
            label: is_string($body['label'] ?? null) ? $body['label'] : null,
            currency: Currency::from($currency),
            amount: $amount,
            currencyAmount: $currencyAmount,
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            exchangeRate: $exchangeRate,
            currencyTax: $currencyTax,
            language: BillingLanguage::from($language),
            customerInvoiceTemplateId: is_array($customerInvoiceTemplate) ? $customerInvoiceTemplate['id'] : null,
            discount: Discount::fromArray($discount),
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: is_string($body['pdf_description'] ?? null) ? $body['pdf_description'] : null,
            specialMention: is_string($body['special_mention'] ?? null) ? $body['special_mention'] : null,
            invoiceLineSectionsUrl: $invoiceLineSections['url'],
            invoiceLinesUrl: $invoiceLines['url'],
        );
    }
}
