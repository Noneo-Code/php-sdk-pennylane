<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

final readonly class IndividualCustomerDto implements CustomerDto
{
    /** @param string[] $emails */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $billingIban,
        public PaymentConditions $paymentConditions,
        public string $recipient,
        public string $phone,
        public ?string $reference,
        public ?string $notes,
        public string $firstName,
        public string $lastName,
        public ?int $ledgerAccountId,
        public array $emails,
        public Address $billingAddress,
        public Address $deliveryAddress,
        public string $createdAt,
        public string $updatedAt,
        public string $externalReference,
        public BillingLanguage $billingLanguage,
        public string $mandatesUrl,
        public string $proAccountMandatesUrl,
        public string $contactsUrl,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $billingIban = $body['billing_iban'] ?? null;
        $paymentConditions = $body['payment_conditions'] ?? null;
        $recipient = $body['recipient'] ?? null;
        $phone = $body['phone'] ?? null;
        $reference = $body['reference'] ?? null;
        $notes = $body['notes'] ?? null;
        $firstName = $body['first_name'] ?? null;
        $lastName = $body['last_name'] ?? null;
        $ledgerAccount = $body['ledger_account'] ?? null;
        $emails = $body['emails'] ?? null;
        $billingAddress = $body['billing_address'] ?? null;
        $deliveryAddress = $body['delivery_address'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $externalReference = $body['external_reference'] ?? null;
        $billingLanguage = $body['billing_language'] ?? null;
        $mandates = $body['mandates'] ?? null;
        $proAccountMandates = $body['pro_account_mandates'] ?? null;
        $contacts = $body['contacts'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($name)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "name" textuel.');
        }

        if (null !== $billingIban && !is_string($billingIban)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "billing_iban" textuel ou nul.');
        }

        if (!is_string($paymentConditions)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "payment_conditions" textuelles.');
        }

        if (!is_string($recipient)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "recipient" textuel.');
        }

        if (!is_string($phone)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "phone" textuel.');
        }

        if (null !== $reference && !is_string($reference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "reference" textuelle ou nulle.');
        }

        if (null !== $notes && !is_string($notes)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "notes" textuelles ou nulles.');
        }

        if (!is_string($firstName)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "first_name" textuel.');
        }

        if (!is_string($lastName)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "last_name" textuel.');
        }

        if (null !== $ledgerAccount && !is_array($ledgerAccount)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_account" objet ou nul.');
        }

        if (!is_array($emails)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "emails".');
        }

        if (!is_array($billingAddress)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "billing_address".');
        }

        if (!is_array($deliveryAddress)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "delivery_address".');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        if (!is_string($externalReference)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "external_reference" textuel.');
        }

        if (!is_string($billingLanguage)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "billing_language" textuelle.');
        }

        if (!is_array($mandates)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "mandates".');
        }

        if (!is_array($proAccountMandates)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "pro_account_mandates".');
        }

        if (!is_array($contacts)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "contacts".');
        }

        $ledgerAccountId = null;

        if (null !== $ledgerAccount) {
            $rawLedgerAccountId = $ledgerAccount['id'] ?? null;

            if (!is_int($rawLedgerAccountId)) {
                throw new \InvalidArgumentException('Le "ledger_account" de la réponse Pennylane doit contenir un "id" entier.');
            }

            $ledgerAccountId = $rawLedgerAccountId;
        }

        $mandatesUrl = $mandates['url'] ?? null;
        $proAccountMandatesUrl = $proAccountMandates['url'] ?? null;
        $contactsUrl = $contacts['url'] ?? null;

        if (!is_string($mandatesUrl)) {
            throw new \InvalidArgumentException('Le "mandates" de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        if (!is_string($proAccountMandatesUrl)) {
            throw new \InvalidArgumentException('Le "pro_account_mandates" de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        if (!is_string($contactsUrl)) {
            throw new \InvalidArgumentException('Le "contacts" de la réponse Pennylane doit contenir une "url" textuelle.');
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
            billingIban: $billingIban,
            paymentConditions: PaymentConditions::from($paymentConditions),
            recipient: $recipient,
            phone: $phone,
            reference: $reference,
            notes: $notes,
            firstName: $firstName,
            lastName: $lastName,
            ledgerAccountId: $ledgerAccountId,
            emails: $validatedEmails,
            billingAddress: Address::fromArray($billingAddress),
            deliveryAddress: Address::fromArray($deliveryAddress),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            externalReference: $externalReference,
            billingLanguage: BillingLanguage::from($billingLanguage),
            mandatesUrl: $mandatesUrl,
            proAccountMandatesUrl: $proAccountMandatesUrl,
            contactsUrl: $contactsUrl,
        );
    }

    public function customerType(): CustomerType
    {
        return CustomerType::Individual;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function billingIban(): ?string
    {
        return $this->billingIban;
    }

    public function paymentConditions(): PaymentConditions
    {
        return $this->paymentConditions;
    }

    public function recipient(): string
    {
        return $this->recipient;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function reference(): ?string
    {
        return $this->reference;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function ledgerAccountId(): ?int
    {
        return $this->ledgerAccountId;
    }

    /** @return string[] */
    public function emails(): array
    {
        return $this->emails;
    }

    public function billingAddress(): Address
    {
        return $this->billingAddress;
    }

    public function deliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    public function externalReference(): string
    {
        return $this->externalReference;
    }

    public function billingLanguage(): BillingLanguage
    {
        return $this->billingLanguage;
    }

    public function mandatesUrl(): string
    {
        return $this->mandatesUrl;
    }

    public function proAccountMandatesUrl(): string
    {
        return $this->proAccountMandatesUrl;
    }

    public function contactsUrl(): string
    {
        return $this->contactsUrl;
    }
}
