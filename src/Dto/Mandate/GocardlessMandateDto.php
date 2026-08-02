<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

final readonly class GocardlessMandateDto
{
    public function __construct(
        public int $id,
        public ?string $externalReference,
        public ?int $customerId,
        public ?string $customerUrl,
        public GocardlessMandateStatus $status,
        public string $externalCustomerAccount,
        public ?string $externalCustomerLabel,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $customer = $body['customer'] ?? null;
        $status = $body['status'] ?? null;
        $externalCustomerAccount = $body['external_customer_account'] ?? null;
        $externalCustomerLabel = $body['external_customer_label'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le mandat GoCardless de la réponse Pennylane doit contenir un entier "id".');
        }

        if (null !== $externalReference && !is_string($externalReference)) {
            throw new \InvalidArgumentException('L\'"external_reference" du mandat GoCardless Pennylane doit être textuel ou nul.');
        }

        if (null !== $customer && !is_array($customer)) {
            throw new \InvalidArgumentException('Le "customer" du mandat GoCardless Pennylane doit être un objet ou nul.');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le mandat GoCardless de la réponse Pennylane doit contenir un "status".');
        }

        if (!is_string($externalCustomerAccount)) {
            throw new \InvalidArgumentException('Le mandat GoCardless de la réponse Pennylane doit contenir un "external_customer_account".');
        }

        if (null !== $externalCustomerLabel && !is_string($externalCustomerLabel)) {
            throw new \InvalidArgumentException('L\'"external_customer_label" du mandat GoCardless Pennylane doit être textuel ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le mandat GoCardless de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            externalReference: $externalReference,
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
            status: GocardlessMandateStatus::from($status),
            externalCustomerAccount: $externalCustomerAccount,
            externalCustomerLabel: $externalCustomerLabel,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
