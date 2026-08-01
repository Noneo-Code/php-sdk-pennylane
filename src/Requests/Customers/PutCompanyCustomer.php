<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Customers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutCompanyCustomer extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param string[]|null $emails */
    public function __construct(
        private readonly int $id,
        private readonly ?string $name = null,
        private readonly ?string $vatNumber = null,
        private readonly ?string $regNo = null,
        private readonly ?string $phone = null,
        private readonly ?Address $billingAddress = null,
        private readonly ?Address $deliveryAddress = null,
        private readonly ?PaymentConditions $paymentConditions = null,
        private readonly ?string $billingIban = null,
        private readonly ?string $recipient = null,
        private readonly ?string $reference = null,
        private readonly ?string $notes = null,
        private readonly ?array $emails = null,
        private readonly ?string $externalReference = null,
        private readonly ?BillingLanguage $billingLanguage = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/company_customers/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'name' => $this->name,
            'vat_number' => $this->vatNumber,
            'reg_no' => $this->regNo,
            'phone' => $this->phone,
            'billing_address' => $this->billingAddress?->toArray(),
            'delivery_address' => $this->deliveryAddress?->toArray(),
            'payment_conditions' => $this->paymentConditions?->value,
            'billing_iban' => $this->billingIban,
            'recipient' => $this->recipient,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'emails' => $this->emails,
            'external_reference' => $this->externalReference,
            'billing_language' => $this->billingLanguage?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
