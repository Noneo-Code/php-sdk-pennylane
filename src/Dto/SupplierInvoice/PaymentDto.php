<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoicePaymentStatus;

final readonly class PaymentDto
{
    public function __construct(
        public int $id,
        public string $label,
        public ?Currency $currency,
        public string $currencyAmount,
        public CustomerInvoicePaymentStatus $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $currency = $body['currency'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $status = $body['status'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id) || !is_string($label)) {
            throw new \InvalidArgumentException('Le paiement de la réponse Pennylane doit contenir un "id" entier et un "label" textuel.');
        }

        if (null !== $currency && !is_string($currency)) {
            throw new \InvalidArgumentException('La "currency" du paiement Pennylane doit être textuelle ou nulle.');
        }

        if (!is_string($currencyAmount)) {
            throw new \InvalidArgumentException('Le paiement de la réponse Pennylane doit contenir un "currency_amount" textuel.');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le paiement de la réponse Pennylane doit contenir un "status" textuel.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le paiement de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            label: $label,
            currency: null !== $currency ? Currency::from($currency) : null,
            currencyAmount: $currencyAmount,
            status: CustomerInvoicePaymentStatus::from($status),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
