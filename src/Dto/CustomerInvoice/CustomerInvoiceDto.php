<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;

final readonly class CustomerInvoiceDto
{
    public function __construct(
        public int $id,
        public ?string $label,
        public string $invoiceNumber,
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
        public bool $paid,
        public CustomerInvoiceStatus $status,
        public Discount $discount,
        public int $ledgerEntryId,
        public ?string $publicFileUrl,
        public ?string $filename,
        public ?string $remainingAmountWithTax,
        public ?string $remainingAmountWithoutTax,
        public bool $draft,
        public ?string $specialMention,
        public ?int $customerId,
        public ?string $customerUrl,
        public string $invoiceLineSectionsUrl,
        public string $invoiceLinesUrl,
        public string $customHeaderFieldsUrl,
        public string $categoriesUrl,
        public string $pdfInvoiceFreeText,
        public string $pdfInvoiceSubject,
        public ?string $pdfDescription,
        public ?int $billingSubscriptionId,
        public ?int $creditedInvoiceId,
        public ?string $creditedInvoiceUrl,
        public ?int $customerInvoiceTemplateId,
        public ?TransactionReferenceDto $transactionReference,
        public string $paymentsUrl,
        public string $matchedTransactionsUrl,
        public string $appendicesUrl,
        public ?int $quoteId,
        public string $externalReference,
        public ?EInvoicingStatus $eInvoicingStatus,
        public ?string $eInvoicingReason,
        public ?string $eInvoicingFlowId,
        public bool $facturX,
        public ?SchematronValidationStatus $schematronValidationStatus,
        public ?string $archivedAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $invoiceNumber = $body['invoice_number'] ?? null;
        $currency = $body['currency'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $exchangeRate = $body['exchange_rate'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $tax = $body['tax'] ?? null;
        $language = $body['language'] ?? null;
        $paid = $body['paid'] ?? null;
        $status = $body['status'] ?? null;
        $discount = $body['discount'] ?? null;
        $ledgerEntry = $body['ledger_entry'] ?? null;
        $draft = $body['draft'] ?? null;
        $customer = $body['customer'] ?? null;
        $invoiceLineSections = $body['invoice_line_sections'] ?? null;
        $invoiceLines = $body['invoice_lines'] ?? null;
        $customHeaderFields = $body['custom_header_fields'] ?? null;
        $categories = $body['categories'] ?? null;
        $pdfInvoiceFreeText = $body['pdf_invoice_free_text'] ?? null;
        $pdfInvoiceSubject = $body['pdf_invoice_subject'] ?? null;
        $billingSubscription = $body['billing_subscription'] ?? null;
        $creditedInvoice = $body['credited_invoice'] ?? null;
        $customerInvoiceTemplate = $body['customer_invoice_template'] ?? null;
        $transactionReference = $body['transaction_reference'] ?? null;
        $payments = $body['payments'] ?? null;
        $matchedTransactions = $body['matched_transactions'] ?? null;
        $appendices = $body['appendices'] ?? null;
        $quote = $body['quote'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $eInvoicing = $body['e_invoicing'] ?? null;
        $facturX = $body['factur_x'] ?? null;
        $schematronValidationStatus = $body['schematron_validation_status'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($invoiceNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "invoice_number" textuel.');
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

        if (!is_bool($paid)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "paid".');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel.');
        }

        if (!is_array($discount)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "discount".');
        }

        if (!is_array($ledgerEntry) || !is_int($ledgerEntry['id'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_entry" avec un "id" entier.');
        }

        if (!is_bool($draft)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "draft".');
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

        if (!is_array($customHeaderFields) || !is_string($customHeaderFields['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "custom_header_fields" avec une "url" textuelle.');
        }

        if (!is_array($categories) || !is_string($categories['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "categories" avec une "url" textuelle.');
        }

        if (!is_string($pdfInvoiceFreeText) || !is_string($pdfInvoiceSubject)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "pdf_invoice_free_text" et "pdf_invoice_subject" sous forme de texte.');
        }

        if (null !== $billingSubscription && !is_array($billingSubscription)) {
            throw new \InvalidArgumentException('La "billing_subscription" de la réponse Pennylane doit être un objet ou nulle.');
        }

        if (null !== $creditedInvoice && !is_array($creditedInvoice)) {
            throw new \InvalidArgumentException('La "credited_invoice" de la réponse Pennylane doit être un objet ou nulle.');
        }

        if (null !== $customerInvoiceTemplate && !is_array($customerInvoiceTemplate)) {
            throw new \InvalidArgumentException('Le "customer_invoice_template" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (null !== $transactionReference && !is_array($transactionReference)) {
            throw new \InvalidArgumentException('La "transaction_reference" de la réponse Pennylane doit être un objet ou nulle.');
        }

        if (!is_array($payments) || !is_string($payments['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "payments" avec une "url" textuelle.');
        }

        if (!is_array($matchedTransactions) || !is_string($matchedTransactions['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "matched_transactions" avec une "url" textuelle.');
        }

        if (!is_array($appendices) || !is_string($appendices['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "appendices" avec une "url" textuelle.');
        }

        if (null !== $quote && !is_array($quote)) {
            throw new \InvalidArgumentException('Le "quote" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_string($externalReference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "external_reference" textuelle.');
        }

        if (null !== $eInvoicing && !is_array($eInvoicing)) {
            throw new \InvalidArgumentException('L\'"e_invoicing" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_bool($facturX)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "factur_x".');
        }

        if (null !== $schematronValidationStatus && !is_string($schematronValidationStatus)) {
            throw new \InvalidArgumentException('Le "schematron_validation_status" de la réponse Pennylane doit être textuel ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        $eInvoicingFlow = is_array($eInvoicing) ? ($eInvoicing['flow'] ?? null) : null;

        return new self(
            id: $id,
            label: is_string($body['label'] ?? null) ? $body['label'] : null,
            invoiceNumber: $invoiceNumber,
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
            paid: $paid,
            status: CustomerInvoiceStatus::from($status),
            discount: Discount::fromArray($discount),
            ledgerEntryId: $ledgerEntry['id'],
            publicFileUrl: is_string($body['public_file_url'] ?? null) ? $body['public_file_url'] : null,
            filename: is_string($body['filename'] ?? null) ? $body['filename'] : null,
            remainingAmountWithTax: is_string($body['remaining_amount_with_tax'] ?? null) ? $body['remaining_amount_with_tax'] : null,
            remainingAmountWithoutTax: is_string($body['remaining_amount_without_tax'] ?? null) ? $body['remaining_amount_without_tax'] : null,
            draft: $draft,
            specialMention: is_string($body['special_mention'] ?? null) ? $body['special_mention'] : null,
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
            invoiceLineSectionsUrl: $invoiceLineSections['url'],
            invoiceLinesUrl: $invoiceLines['url'],
            customHeaderFieldsUrl: $customHeaderFields['url'],
            categoriesUrl: $categories['url'],
            pdfInvoiceFreeText: $pdfInvoiceFreeText,
            pdfInvoiceSubject: $pdfInvoiceSubject,
            pdfDescription: is_string($body['pdf_description'] ?? null) ? $body['pdf_description'] : null,
            billingSubscriptionId: is_array($billingSubscription) && is_int($billingSubscription['id'] ?? null) ? $billingSubscription['id'] : null,
            creditedInvoiceId: is_array($creditedInvoice) && is_int($creditedInvoice['id'] ?? null) ? $creditedInvoice['id'] : null,
            creditedInvoiceUrl: is_array($creditedInvoice) && is_string($creditedInvoice['url'] ?? null) ? $creditedInvoice['url'] : null,
            customerInvoiceTemplateId: is_array($customerInvoiceTemplate) && is_int($customerInvoiceTemplate['id'] ?? null) ? $customerInvoiceTemplate['id'] : null,
            transactionReference: is_array($transactionReference) ? TransactionReferenceDto::fromArray($transactionReference) : null,
            paymentsUrl: $payments['url'],
            matchedTransactionsUrl: $matchedTransactions['url'],
            appendicesUrl: $appendices['url'],
            quoteId: is_array($quote) && is_int($quote['id'] ?? null) ? $quote['id'] : null,
            externalReference: $externalReference,
            eInvoicingStatus: is_array($eInvoicing) && is_string($eInvoicing['status'] ?? null) ? EInvoicingStatus::from($eInvoicing['status']) : null,
            eInvoicingReason: is_array($eInvoicing) && is_string($eInvoicing['reason'] ?? null) ? $eInvoicing['reason'] : null,
            eInvoicingFlowId: is_array($eInvoicingFlow) && is_string($eInvoicingFlow['id'] ?? null) ? $eInvoicingFlow['id'] : null,
            facturX: $facturX,
            schematronValidationStatus: null !== $schematronValidationStatus ? SchematronValidationStatus::from($schematronValidationStatus) : null,
            archivedAt: is_string($body['archived_at'] ?? null) ? $body['archived_at'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
