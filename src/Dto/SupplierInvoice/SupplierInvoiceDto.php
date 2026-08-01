<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\TransactionReferenceDto;

final readonly class SupplierInvoiceDto
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
        public bool $reconciled,
        public SupplierInvoiceAccountingStatus $accountingStatus,
        public ?string $filename,
        public ?string $publicFileUrl,
        public ?string $remainingAmountWithTax,
        public ?string $remainingAmountWithoutTax,
        public int $ledgerEntryId,
        public ?int $supplierId,
        public ?string $supplierUrl,
        public string $invoiceLinesUrl,
        public string $categoriesUrl,
        public ?TransactionReferenceDto $transactionReference,
        public SupplierInvoicePaymentStatus $paymentStatus,
        public ?bool $paid,
        public string $paymentsUrl,
        public string $matchedTransactionsUrl,
        public string $externalReference,
        public ?ImportSourceDto $importSource,
        public ?SupplierEInvoicingStatus $eInvoicingStatus,
        public ?string $eInvoicingReason,
        public ?string $eInvoicingFlowId,
        public ?string $eInvoicingSourceFileUrl,
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
        $reconciled = $body['reconciled'] ?? null;
        $accountingStatus = $body['accounting_status'] ?? null;
        $ledgerEntry = $body['ledger_entry'] ?? null;
        $supplier = $body['supplier'] ?? null;
        $invoiceLines = $body['invoice_lines'] ?? null;
        $categories = $body['categories'] ?? null;
        $transactionReference = $body['transaction_reference'] ?? null;
        $paymentStatus = $body['payment_status'] ?? null;
        $payments = $body['payments'] ?? null;
        $matchedTransactions = $body['matched_transactions'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $importSource = $body['import_source'] ?? null;
        $eInvoicing = $body['e_invoicing'] ?? null;
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

        if (!is_bool($reconciled)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "reconciled".');
        }

        if (!is_string($accountingStatus)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "accounting_status" textuel.');
        }

        if (!is_array($ledgerEntry) || !is_int($ledgerEntry['id'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_entry" avec un "id" entier.');
        }

        if (null !== $supplier && !is_array($supplier)) {
            throw new \InvalidArgumentException('Le "supplier" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_array($invoiceLines) || !is_string($invoiceLines['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "invoice_lines" avec une "url" textuelle.');
        }

        if (!is_array($categories) || !is_string($categories['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "categories" avec une "url" textuelle.');
        }

        if (null !== $transactionReference && !is_array($transactionReference)) {
            throw new \InvalidArgumentException('La "transaction_reference" de la réponse Pennylane doit être un objet ou nulle.');
        }

        if (!is_string($paymentStatus)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "payment_status" textuel.');
        }

        if (!is_array($payments) || !is_string($payments['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "payments" avec une "url" textuelle.');
        }

        if (!is_array($matchedTransactions) || !is_string($matchedTransactions['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "matched_transactions" avec une "url" textuelle.');
        }

        if (!is_string($externalReference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "external_reference" textuelle.');
        }

        if (null !== $importSource && !is_array($importSource)) {
            throw new \InvalidArgumentException('L\'"import_source" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (null !== $eInvoicing && !is_array($eInvoicing)) {
            throw new \InvalidArgumentException('L\'"e_invoicing" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        $eInvoicingFlow = is_array($eInvoicing) ? ($eInvoicing['flow'] ?? null) : null;
        $paid = $body['paid'] ?? null;

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
            reconciled: $reconciled,
            accountingStatus: SupplierInvoiceAccountingStatus::from($accountingStatus),
            filename: is_string($body['filename'] ?? null) ? $body['filename'] : null,
            publicFileUrl: is_string($body['public_file_url'] ?? null) ? $body['public_file_url'] : null,
            remainingAmountWithTax: is_string($body['remaining_amount_with_tax'] ?? null) ? $body['remaining_amount_with_tax'] : null,
            remainingAmountWithoutTax: is_string($body['remaining_amount_without_tax'] ?? null) ? $body['remaining_amount_without_tax'] : null,
            ledgerEntryId: $ledgerEntry['id'],
            supplierId: is_array($supplier) && is_int($supplier['id'] ?? null) ? $supplier['id'] : null,
            supplierUrl: is_array($supplier) && is_string($supplier['url'] ?? null) ? $supplier['url'] : null,
            invoiceLinesUrl: $invoiceLines['url'],
            categoriesUrl: $categories['url'],
            transactionReference: is_array($transactionReference) ? TransactionReferenceDto::fromArray($transactionReference) : null,
            paymentStatus: SupplierInvoicePaymentStatus::from($paymentStatus),
            paid: is_bool($paid) ? $paid : null,
            paymentsUrl: $payments['url'],
            matchedTransactionsUrl: $matchedTransactions['url'],
            externalReference: $externalReference,
            importSource: is_array($importSource) ? ImportSourceDto::fromArray($importSource) : null,
            eInvoicingStatus: is_array($eInvoicing) && is_string($eInvoicing['status'] ?? null) ? SupplierEInvoicingStatus::from($eInvoicing['status']) : null,
            eInvoicingReason: is_array($eInvoicing) && is_string($eInvoicing['reason'] ?? null) ? $eInvoicing['reason'] : null,
            eInvoicingFlowId: is_array($eInvoicingFlow) && is_string($eInvoicingFlow['id'] ?? null) ? $eInvoicingFlow['id'] : null,
            eInvoicingSourceFileUrl: is_array($eInvoicing) && is_string($eInvoicing['source_file_url'] ?? null) ? $eInvoicing['source_file_url'] : null,
            archivedAt: is_string($body['archived_at'] ?? null) ? $body['archived_at'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
