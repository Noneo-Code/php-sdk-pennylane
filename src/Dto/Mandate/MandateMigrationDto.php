<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

final readonly class MandateMigrationDto
{
    public function __construct(
        public int $id,
        public string $status,
        public ?DirectDebitMethod $directDebitMethod,
        public ?string $signedAt,
        public ?string $errorMessage,
        public ?string $migratedAt,
        public ?string $migrationStartedAt,
        public string $createdAt,
        public string $updatedAt,
        public int $mandateId,
        public MandateType $mandateType,
        public ?int $customerId,
        public ?string $customerUrl,
        public ?string $proAccountMandateUrl,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $status = $body['status'] ?? null;
        $directDebitMethod = $body['direct_debit_method'] ?? null;
        $signedAt = $body['signed_at'] ?? null;
        $errorMessage = $body['error_message'] ?? null;
        $migratedAt = $body['migrated_at'] ?? null;
        $migrationStartedAt = $body['migration_started_at'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $mandate = $body['mandate'] ?? null;
        $customer = $body['customer'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('La migration de mandat Pennylane doit contenir un entier "id".');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('La migration de mandat Pennylane doit contenir un "status".');
        }

        if (null !== $directDebitMethod && !is_string($directDebitMethod)) {
            throw new \InvalidArgumentException('Le "direct_debit_method" de la migration de mandat Pennylane doit être textuel ou nul.');
        }

        if (null !== $signedAt && !is_string($signedAt)) {
            throw new \InvalidArgumentException('Le "signed_at" de la migration de mandat Pennylane doit être textuel ou nul.');
        }

        if (null !== $errorMessage && !is_string($errorMessage)) {
            throw new \InvalidArgumentException('L\'"error_message" de la migration de mandat Pennylane doit être textuel ou nul.');
        }

        if (null !== $migratedAt && !is_string($migratedAt)) {
            throw new \InvalidArgumentException('Le "migrated_at" de la migration de mandat Pennylane doit être textuel ou nul.');
        }

        if (null !== $migrationStartedAt && !is_string($migrationStartedAt)) {
            throw new \InvalidArgumentException('Le "migration_started_at" de la migration de mandat Pennylane doit être textuel ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('La migration de mandat Pennylane doit contenir "created_at" et "updated_at".');
        }

        if (!is_array($mandate) || !is_int($mandate['id'] ?? null) || !is_string($mandate['type'] ?? null)) {
            throw new \InvalidArgumentException('La migration de mandat Pennylane doit contenir un "mandate" avec un "id" et un "type".');
        }

        // `customer` est documenté nullable : une migration peut ne pointer
        // aucun client. Refuser ce cas ferait échouer la page entière.
        if (null !== $customer && (!is_array($customer) || !is_int($customer['id'] ?? null) || !is_string($customer['url'] ?? null))) {
            throw new \InvalidArgumentException('La migration de mandat Pennylane doit contenir un "customer" avec un "id" et une "url", ou nul.');
        }

        $proAccountMandate = is_array($customer) ? $customer['pro_account_mandate'] ?? null : null;

        if (null !== $customer && (!is_array($proAccountMandate) || !is_string($proAccountMandate['url'] ?? null))) {
            throw new \InvalidArgumentException('Le "customer" de la migration de mandat Pennylane doit contenir un "pro_account_mandate" avec une "url".');
        }

        return new self(
            id: $id,
            status: $status,
            directDebitMethod: null !== $directDebitMethod ? DirectDebitMethod::from($directDebitMethod) : null,
            signedAt: $signedAt,
            errorMessage: $errorMessage,
            migratedAt: $migratedAt,
            migrationStartedAt: $migrationStartedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            mandateId: $mandate['id'],
            mandateType: MandateType::from($mandate['type']),
            customerId: is_array($customer) ? $customer['id'] : null,
            customerUrl: is_array($customer) ? $customer['url'] : null,
            proAccountMandateUrl: is_array($proAccountMandate) ? $proAccountMandate['url'] : null,
        );
    }
}
