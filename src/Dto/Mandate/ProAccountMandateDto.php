<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

final readonly class ProAccountMandateDto
{
    public function __construct(
        public string $status,
        public bool $earlyExecutionDatePermitted,
        public bool $activeBillingSubscription,
        public ?string $signedAt,
        public string $createdAt,
        public ?string $pdfUrl,
        public ?int $customerId,
        public ?string $customerUrl,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $status = $body['status'] ?? null;
        $earlyExecutionDatePermitted = $body['early_execution_date_permitted'] ?? null;
        $activeBillingSubscription = $body['active_billing_subscription'] ?? null;
        $signedAt = $body['signed_at'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $pdfUrl = $body['pdf_url'] ?? null;
        $customer = $body['customer'] ?? null;

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le mandat Pro Account de la réponse Pennylane doit contenir un "status".');
        }

        if (!is_bool($earlyExecutionDatePermitted) || !is_bool($activeBillingSubscription)) {
            throw new \InvalidArgumentException('Le mandat Pro Account de la réponse Pennylane doit contenir "early_execution_date_permitted" et "active_billing_subscription" booléens.');
        }

        if (null !== $signedAt && !is_string($signedAt)) {
            throw new \InvalidArgumentException('Le "signed_at" du mandat Pro Account Pennylane doit être textuel ou nul.');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le mandat Pro Account de la réponse Pennylane doit contenir un "created_at".');
        }

        if (null !== $pdfUrl && !is_string($pdfUrl)) {
            throw new \InvalidArgumentException('Le "pdf_url" du mandat Pro Account Pennylane doit être textuel ou nul.');
        }

        if (null !== $customer && !is_array($customer)) {
            throw new \InvalidArgumentException('Le "customer" du mandat Pro Account Pennylane doit être un objet ou nul.');
        }

        return new self(
            status: $status,
            earlyExecutionDatePermitted: $earlyExecutionDatePermitted,
            activeBillingSubscription: $activeBillingSubscription,
            signedAt: $signedAt,
            createdAt: $createdAt,
            pdfUrl: $pdfUrl,
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
        );
    }
}
