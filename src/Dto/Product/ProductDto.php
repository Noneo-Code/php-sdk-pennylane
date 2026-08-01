<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Product;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class ProductDto
{
    public function __construct(
        public int $id,
        public string $label,
        public string $description,
        public string $externalReference,
        public string $priceBeforeTax,
        public VatRate $vatRate,
        public string $price,
        public string $unit,
        public Currency $currency,
        public ?string $reference,
        public ?Substance $substance,
        public ?int $ledgerAccountId,
        public ?string $archivedAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $description = $body['description'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $priceBeforeTax = $body['price_before_tax'] ?? null;
        $vatRate = $body['vat_rate'] ?? null;
        $price = $body['price'] ?? null;
        $unit = $body['unit'] ?? null;
        $currency = $body['currency'] ?? null;
        $substance = $body['substance'] ?? null;
        $ledgerAccount = $body['ledger_account'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($label) || !is_string($description) || !is_string($externalReference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "label", "description" et "external_reference" sous forme de texte.');
        }

        if (!is_string($priceBeforeTax) || !is_string($price)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "price_before_tax" et "price" sous forme de texte.');
        }

        if (!is_string($vatRate)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "vat_rate" textuel.');
        }

        if (!is_string($unit)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "unit" textuelle.');
        }

        if (!is_string($currency)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "currency" textuelle.');
        }

        if (null !== $substance && !is_string($substance)) {
            throw new \InvalidArgumentException('La "substance" de la réponse Pennylane doit être textuelle ou nulle.');
        }

        if (null !== $ledgerAccount && (!is_array($ledgerAccount) || !is_int($ledgerAccount['id'] ?? null))) {
            throw new \InvalidArgumentException('Le "ledger_account" de la réponse Pennylane doit être un objet avec un "id" entier, ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            label: $label,
            description: $description,
            externalReference: $externalReference,
            priceBeforeTax: $priceBeforeTax,
            vatRate: VatRate::from($vatRate),
            price: $price,
            unit: $unit,
            currency: Currency::from($currency),
            reference: is_string($body['reference'] ?? null) ? $body['reference'] : null,
            substance: null !== $substance ? Substance::from($substance) : null,
            ledgerAccountId: is_array($ledgerAccount) && is_int($ledgerAccount['id'] ?? null) ? $ledgerAccount['id'] : null,
            archivedAt: is_string($body['archived_at'] ?? null) ? $body['archived_at'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
