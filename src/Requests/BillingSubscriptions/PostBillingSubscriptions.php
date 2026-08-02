<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\BillingSubscriptions;

use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionPaymentMethod;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewInvoiceData;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewRecurringRule;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostBillingSubscriptions extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $start,
        private readonly NewMode $mode,
        private readonly PaymentConditions $paymentConditions,
        private readonly BillingSubscriptionPaymentMethod $paymentMethod,
        private readonly NewRecurringRule $recurringRule,
        private readonly int $customerId,
        private readonly NewInvoiceData $customerInvoiceData,
        private readonly ?string $label = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/billing_subscriptions';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'start' => $this->start,
            'mode' => $this->mode->toArray(),
            'payment_conditions' => $this->paymentConditions->value,
            'payment_method' => $this->paymentMethod->value,
            'label' => $this->label,
            'recurring_rule' => $this->recurringRule->toArray(),
            'customer_id' => $this->customerId,
            'customer_invoice_data' => $this->customerInvoiceData->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
