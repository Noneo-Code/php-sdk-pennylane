<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Supplier;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;

final readonly class SupplierDto
{
    /** @param string[] $emails */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $establishmentNo,
        public ?string $regNo,
        public string $vatNumber,
        public ?int $ledgerAccountId,
        public array $emails,
        public string $iban,
        public Address $postalAddress,
        public ?SupplierPaymentMethod $supplierPaymentMethod,
        public ?int $supplierDueDateDelay,
        public ?SupplierDueDateRule $supplierDueDateRule,
        public string $externalReference,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $establishmentNo = $body['establishment_no'] ?? null;
        $regNo = $body['reg_no'] ?? null;
        $vatNumber = $body['vat_number'] ?? null;
        $ledgerAccount = $body['ledger_account'] ?? null;
        $emails = $body['emails'] ?? null;
        $iban = $body['iban'] ?? null;
        $postalAddress = $body['postal_address'] ?? null;
        $supplierPaymentMethod = $body['supplier_payment_method'] ?? null;
        $supplierDueDateDelay = $body['supplier_due_date_delay'] ?? null;
        $supplierDueDateRule = $body['supplier_due_date_rule'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($name)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "name" textuel.');
        }

        if (null !== $establishmentNo && !is_string($establishmentNo)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "establishment_no" textuel ou nul.');
        }

        if (null !== $regNo && !is_string($regNo)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "reg_no" textuel ou nul.');
        }

        if (!is_string($vatNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "vat_number" textuel.');
        }

        if (null !== $ledgerAccount && !is_array($ledgerAccount)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_account" objet ou nul.');
        }

        if (!is_array($emails)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "emails".');
        }

        if (!is_string($iban)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "iban" textuel.');
        }

        if (!is_array($postalAddress)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "postal_address".');
        }

        if (null !== $supplierPaymentMethod && !is_string($supplierPaymentMethod)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "supplier_payment_method" textuel ou nul.');
        }

        if (null !== $supplierDueDateDelay && !is_int($supplierDueDateDelay)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "supplier_due_date_delay" entier ou nul.');
        }

        if (null !== $supplierDueDateRule && !is_string($supplierDueDateRule)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "supplier_due_date_rule" textuelle ou nulle.');
        }

        if (!is_string($externalReference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "external_reference" textuel.');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        $ledgerAccountId = null;

        if (null !== $ledgerAccount) {
            $rawLedgerAccountId = $ledgerAccount['id'] ?? null;

            if (!is_int($rawLedgerAccountId)) {
                throw new \InvalidArgumentException('Le "ledger_account" de la réponse Pennylane doit contenir un "id" entier.');
            }

            $ledgerAccountId = $rawLedgerAccountId;
        }

        $validatedEmails = [];

        foreach ($emails as $email) {
            if (!is_string($email)) {
                throw new \InvalidArgumentException('Le tableau "emails" de la réponse Pennylane doit contenir des chaînes.');
            }

            $validatedEmails[] = $email;
        }

        return new self(
            id: $id,
            name: $name,
            establishmentNo: $establishmentNo,
            regNo: $regNo,
            vatNumber: $vatNumber,
            ledgerAccountId: $ledgerAccountId,
            emails: $validatedEmails,
            iban: $iban,
            postalAddress: Address::fromArray($postalAddress),
            supplierPaymentMethod: null !== $supplierPaymentMethod ? SupplierPaymentMethod::from($supplierPaymentMethod) : null,
            supplierDueDateDelay: $supplierDueDateDelay,
            supplierDueDateRule: null !== $supplierDueDateRule ? SupplierDueDateRule::from($supplierDueDateRule) : null,
            externalReference: $externalReference,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
