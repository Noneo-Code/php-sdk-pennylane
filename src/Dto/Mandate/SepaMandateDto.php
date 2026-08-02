<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

final readonly class SepaMandateDto
{
    public function __construct(
        public int $id,
        public ?string $bank,
        public string $bic,
        public string $iban,
        public SepaMandateSequenceType $sequenceType,
        public string $signedAt,
        public string $identifier,
        public int $customerId,
        public string $customerUrl,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $bank = $body['bank'] ?? null;
        $bic = $body['bic'] ?? null;
        $iban = $body['iban'] ?? null;
        $sequenceType = $body['sequence_type'] ?? null;
        $signedAt = $body['signed_at'] ?? null;
        $identifier = $body['identifier'] ?? null;
        $customer = $body['customer'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le mandat SEPA de la réponse Pennylane doit contenir un entier "id".');
        }

        if (null !== $bank && !is_string($bank)) {
            throw new \InvalidArgumentException('Le "bank" du mandat SEPA Pennylane doit être textuel ou nul.');
        }

        if (!is_string($bic) || !is_string($iban)) {
            throw new \InvalidArgumentException('Le mandat SEPA de la réponse Pennylane doit contenir "bic" et "iban".');
        }

        if (!is_string($sequenceType)) {
            throw new \InvalidArgumentException('Le mandat SEPA de la réponse Pennylane doit contenir un "sequence_type".');
        }

        if (!is_string($signedAt) || !is_string($identifier)) {
            throw new \InvalidArgumentException('Le mandat SEPA de la réponse Pennylane doit contenir "signed_at" et "identifier".');
        }

        if (!is_array($customer) || !is_int($customer['id'] ?? null) || !is_string($customer['url'] ?? null)) {
            throw new \InvalidArgumentException('Le mandat SEPA de la réponse Pennylane doit contenir un "customer" avec un "id" et une "url".');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le mandat SEPA de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            bank: $bank,
            bic: $bic,
            iban: $iban,
            sequenceType: SepaMandateSequenceType::from($sequenceType),
            signedAt: $signedAt,
            identifier: $identifier,
            customerId: $customer['id'],
            customerUrl: $customer['url'],
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
