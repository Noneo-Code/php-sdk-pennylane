<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class TransactionReferenceDto
{
    public function __construct(
        public string $bankingProvider,
        public string $providerFieldName,
        public string $providerFieldValue,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $bankingProvider = $body['banking_provider'] ?? null;
        $providerFieldName = $body['provider_field_name'] ?? null;
        $providerFieldValue = $body['provider_field_value'] ?? null;

        if (!is_string($bankingProvider)) {
            throw new \InvalidArgumentException('La "transaction_reference" de la réponse Pennylane doit contenir un "banking_provider" textuel.');
        }

        if (!is_string($providerFieldName)) {
            throw new \InvalidArgumentException('La "transaction_reference" de la réponse Pennylane doit contenir un "provider_field_name" textuel.');
        }

        if (!is_string($providerFieldValue)) {
            throw new \InvalidArgumentException('La "transaction_reference" de la réponse Pennylane doit contenir une "provider_field_value" textuelle.');
        }

        return new self($bankingProvider, $providerFieldName, $providerFieldValue);
    }
}
