<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Quote;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class QuoteInvoiceLineDto
{
    public function __construct(
        public int $id,
        public string $label,
        public ?string $unit,
        public string $quantity,
        public string $amount,
        public string $currencyAmount,
        public string $description,
        public ?int $productId,
        public ?string $productUrl,
        public VatRate $vatRate,
        public string $currencyAmountBeforeTax,
        public string $currencyTax,
        public string $tax,
        public string $rawCurrencyUnitPrice,
        public Discount $discount,
        public ?int $sectionRank,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $quantity = $body['quantity'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $description = $body['description'] ?? null;
        $product = $body['product'] ?? null;
        $vatRate = $body['vat_rate'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $tax = $body['tax'] ?? null;
        $rawCurrencyUnitPrice = $body['raw_currency_unit_price'] ?? null;
        $discount = $body['discount'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id) || !is_string($label) || !is_string($quantity) || !is_string($amount) || !is_string($currencyAmount) || !is_string($description)) {
            throw new \InvalidArgumentException('La ligne de devis de la réponse Pennylane doit contenir "id", "label", "quantity", "amount", "currency_amount" et "description".');
        }

        if (null !== $product && !is_array($product)) {
            throw new \InvalidArgumentException('Le "product" de la ligne de devis Pennylane doit être un objet ou nul.');
        }

        if (!is_string($vatRate)) {
            throw new \InvalidArgumentException('La ligne de devis de la réponse Pennylane doit contenir un "vat_rate" textuel.');
        }

        if (!is_string($currencyAmountBeforeTax) || !is_string($currencyTax) || !is_string($tax) || !is_string($rawCurrencyUnitPrice)) {
            throw new \InvalidArgumentException('La ligne de devis de la réponse Pennylane doit contenir ses montants sous forme de texte.');
        }

        if (!is_array($discount)) {
            throw new \InvalidArgumentException('La ligne de devis de la réponse Pennylane doit contenir un objet "discount".');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('La ligne de devis de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            label: $label,
            unit: is_string($body['unit'] ?? null) ? $body['unit'] : null,
            quantity: $quantity,
            amount: $amount,
            currencyAmount: $currencyAmount,
            description: $description,
            productId: is_array($product) && is_int($product['id'] ?? null) ? $product['id'] : null,
            productUrl: is_array($product) && is_string($product['url'] ?? null) ? $product['url'] : null,
            vatRate: VatRate::from($vatRate),
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            currencyTax: $currencyTax,
            tax: $tax,
            rawCurrencyUnitPrice: $rawCurrencyUnitPrice,
            discount: Discount::fromArray($discount),
            sectionRank: is_int($body['section_rank'] ?? null) ? $body['section_rank'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
