<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BankAccount;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;

final readonly class BankAccountDto
{
    public function __construct(
        public int $id,
        public string $name,
        public Currency $currency,
        public ?string $balance,
        public string $createdAt,
        public string $updatedAt,
        public int $bankEstablishmentId,
        public ?int $journalId,
        public ?string $journalUrl,
        public int $ledgerAccountId,
        public string $ledgerAccountUrl,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $currency = $body['currency'] ?? null;
        $balance = $body['balance'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $bankEstablishment = $body['bank_establishment'] ?? null;
        $journal = $body['journal'] ?? null;
        $ledgerAccount = $body['ledger_account'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($name)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "name" textuel.');
        }

        if (!is_string($currency)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "currency" textuelle.');
        }

        if (null !== $balance && !is_string($balance)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "balance" textuel ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        if (!is_array($bankEstablishment) || !is_int($bankEstablishment['id'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "bank_establishment" avec un "id" entier.');
        }

        if (null !== $journal && !is_array($journal)) {
            throw new \InvalidArgumentException('Le "journal" du compte bancaire Pennylane doit être un objet ou nul.');
        }

        if (!is_array($ledgerAccount) || !is_int($ledgerAccount['id'] ?? null) || !is_string($ledgerAccount['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_account" avec un "id" et une "url".');
        }

        return new self(
            id: $id,
            name: $name,
            currency: Currency::from($currency),
            balance: $balance,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            bankEstablishmentId: $bankEstablishment['id'],
            journalId: is_array($journal) && is_int($journal['id'] ?? null) ? $journal['id'] : null,
            journalUrl: is_array($journal) && is_string($journal['url'] ?? null) ? $journal['url'] : null,
            ledgerAccountId: $ledgerAccount['id'],
            ledgerAccountUrl: $ledgerAccount['url'],
        );
    }
}
