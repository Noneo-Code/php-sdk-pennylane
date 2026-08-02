<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Customers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutIndividualCustomer extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param string[]|null $emails */
    public function __construct(
        private readonly int $id,
        private readonly string|Clear|null $firstName = null,
        private readonly string|Clear|null $lastName = null,
        private readonly string|Clear|null $phone = null,
        private readonly Address|Clear|null $billingAddress = null,
        private readonly Address|Clear|null $deliveryAddress = null,
        private readonly PaymentConditions|Clear|null $paymentConditions = null,
        private readonly string|Clear|null $billingIban = null,
        private readonly string|Clear|null $recipient = null,
        private readonly string|Clear|null $reference = null,
        private readonly string|Clear|null $notes = null,
        private readonly array|Clear|null $emails = null,
        private readonly string|Clear|null $externalReference = null,
        private readonly BillingLanguage|Clear|null $billingLanguage = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/individual_customers/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'billing_address' => $this->billingAddress,
            'delivery_address' => $this->deliveryAddress,
            'payment_conditions' => $this->paymentConditions,
            'billing_iban' => $this->billingIban,
            'recipient' => $this->recipient,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'emails' => $this->emails,
            'external_reference' => $this->externalReference,
            'billing_language' => $this->billingLanguage,
        ]);
    }
}
