<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImputationDates;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class InvoiceLineDto
{
    public function __construct(
        public int $id,
        public string $label,
        public string $amount,
        public string $currencyAmount,
        public string $description,
        public VatRate $vatRate,
        public string $currencyAmountBeforeTax,
        public string $currencyTax,
        public string $tax,
        public ?ImputationDates $imputationDates,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $description = $body['description'] ?? null;
        $vatRate = $body['vat_rate'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $tax = $body['tax'] ?? null;
        $imputationDates = $body['imputation_dates'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id) || !is_string($label) || !is_string($amount) || !is_string($currencyAmount) || !is_string($description)) {
            throw new \InvalidArgumentException('La ligne de facture fournisseur de la réponse Pennylane doit contenir "id", "label", "amount", "currency_amount" et "description".');
        }

        if (!is_string($vatRate)) {
            throw new \InvalidArgumentException('La ligne de facture fournisseur de la réponse Pennylane doit contenir un "vat_rate" textuel.');
        }

        if (!is_string($currencyAmountBeforeTax) || !is_string($currencyTax) || !is_string($tax)) {
            throw new \InvalidArgumentException('La ligne de facture fournisseur de la réponse Pennylane doit contenir "currency_amount_before_tax", "currency_tax" et "tax".');
        }

        if (null !== $imputationDates && !is_array($imputationDates)) {
            throw new \InvalidArgumentException('Les "imputation_dates" de la ligne de facture fournisseur Pennylane doivent être un objet ou nulles.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('La ligne de facture fournisseur de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            label: $label,
            amount: $amount,
            currencyAmount: $currencyAmount,
            description: $description,
            vatRate: VatRate::from($vatRate),
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            currencyTax: $currencyTax,
            tax: $tax,
            imputationDates: is_array($imputationDates) ? ImputationDates::fromArray($imputationDates) : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
