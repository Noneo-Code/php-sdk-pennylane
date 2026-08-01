<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class NewTransactionReference
{
    public function __construct(
        public TransactionBankingProvider $bankingProvider,
        public TransactionProviderFieldName $providerFieldName,
        public string $providerFieldValue,
    ) {
    }

    /** @return array{banking_provider: string, provider_field_name: string, provider_field_value: string} */
    public function toArray(): array
    {
        return [
            'banking_provider' => $this->bankingProvider->value,
            'provider_field_name' => $this->providerFieldName->value,
            'provider_field_value' => $this->providerFieldValue,
        ];
    }
}
