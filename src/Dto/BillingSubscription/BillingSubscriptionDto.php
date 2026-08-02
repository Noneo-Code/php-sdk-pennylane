<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;

final readonly class BillingSubscriptionDto
{
    public function __construct(
        public int $id,
        public ?string $nextOccurrence,
        public ?string $prevOccurrence,
        public ?string $stoppedAt,
        public ?string $start,
        public ?string $finish,
        public BillingSubscriptionStatus $status,
        public BillingSubscriptionMode $mode,
        public ?string $activatedAt,
        public ?PaymentConditions $paymentConditions,
        public BillingSubscriptionPaymentMethod $paymentMethod,
        public ?string $label,
        public ?EmailSettingsDto $emailSettings,
        public RecurringRuleDto $recurringRule,
        public ?int $customerId,
        public ?string $customerUrl,
        public BillingSubscriptionInvoiceDataDto $invoiceData,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $status = $body['status'] ?? null;
        $mode = $body['mode'] ?? null;
        $paymentConditions = $body['payment_conditions'] ?? null;
        $paymentMethod = $body['payment_method'] ?? null;
        $emailSettings = $body['email_settings'] ?? null;
        $recurringRule = $body['recurring_rule'] ?? null;
        $customer = $body['customer'] ?? null;
        $customerInvoiceData = $body['customer_invoice_data'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($body['id'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel.');
        }

        if (!is_string($mode)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "mode" textuel.');
        }

        if (null !== $paymentConditions && !is_string($paymentConditions)) {
            throw new \InvalidArgumentException('Les "payment_conditions" de la réponse Pennylane doivent être textuelles ou nulles.');
        }

        if (!is_string($paymentMethod)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "payment_method" textuel.');
        }

        if (null !== $emailSettings && !is_array($emailSettings)) {
            throw new \InvalidArgumentException('Les "email_settings" de la réponse Pennylane doivent être un objet ou nulles.');
        }

        if (!is_array($recurringRule)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "recurring_rule".');
        }

        if (null !== $customer && !is_array($customer)) {
            throw new \InvalidArgumentException('Le "customer" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_array($customerInvoiceData)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "customer_invoice_data".');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $body['id'],
            nextOccurrence: is_string($body['next_occurrence'] ?? null) ? $body['next_occurrence'] : null,
            prevOccurrence: is_string($body['prev_occurrence'] ?? null) ? $body['prev_occurrence'] : null,
            stoppedAt: is_string($body['stopped_at'] ?? null) ? $body['stopped_at'] : null,
            start: is_string($body['start'] ?? null) ? $body['start'] : null,
            finish: is_string($body['finish'] ?? null) ? $body['finish'] : null,
            status: BillingSubscriptionStatus::from($status),
            mode: BillingSubscriptionMode::from($mode),
            activatedAt: is_string($body['activated_at'] ?? null) ? $body['activated_at'] : null,
            paymentConditions: null !== $paymentConditions ? PaymentConditions::from($paymentConditions) : null,
            paymentMethod: BillingSubscriptionPaymentMethod::from($paymentMethod),
            label: is_string($body['label'] ?? null) ? $body['label'] : null,
            emailSettings: is_array($emailSettings) ? EmailSettingsDto::fromArray($emailSettings) : null,
            recurringRule: RecurringRuleDto::fromArray($recurringRule),
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
            invoiceData: BillingSubscriptionInvoiceDataDto::fromArray($customerInvoiceData),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
