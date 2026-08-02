<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\BillingSubscriptions;

use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionPaymentMethod;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewRecurringRule;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\UpdateInvoiceData;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutBillingSubscriptions extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly ?bool $stop = null,
        private readonly ?NewMode $mode = null,
        private readonly ?PaymentConditions $paymentConditions = null,
        private readonly ?BillingSubscriptionPaymentMethod $paymentMethod = null,
        private readonly ?string $label = null,
        private readonly ?NewRecurringRule $recurringRule = null,
        private readonly ?int $customerId = null,
        private readonly ?UpdateInvoiceData $customerInvoiceData = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/billing_subscriptions/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'stop' => $this->stop,
            'mode' => $this->mode?->toArray(),
            'payment_conditions' => $this->paymentConditions?->value,
            'payment_method' => $this->paymentMethod?->value,
            'label' => $this->label,
            'recurring_rule' => $this->recurringRule?->toArray(),
            'customer_id' => $this->customerId,
            'customer_invoice_data' => $this->customerInvoiceData?->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
