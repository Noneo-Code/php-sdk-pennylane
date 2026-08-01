<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

interface CustomerDto
{
    public function customerType(): CustomerType;

    public function id(): int;

    public function name(): string;

    public function billingIban(): ?string;

    public function paymentConditions(): PaymentConditions;

    public function recipient(): string;

    public function phone(): string;

    public function reference(): ?string;

    public function notes(): ?string;

    public function ledgerAccountId(): ?int;

    /** @return string[] */
    public function emails(): array;

    public function billingAddress(): Address;

    public function deliveryAddress(): Address;

    public function createdAt(): string;

    public function updatedAt(): string;

    public function externalReference(): string;

    public function billingLanguage(): BillingLanguage;

    public function mandatesUrl(): string;

    public function proAccountMandatesUrl(): string;

    public function contactsUrl(): string;
}
