<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

final readonly class EmailSettingsDto
{
    /** @param list<string> $recipients */
    public function __construct(
        public array $recipients,
        public ?int $billingEmailTemplateId,
        public ?string $billingEmailTemplateLabel,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $recipients = $body['recipients'] ?? null;
        $billingEmailTemplate = $body['billing_email_template'] ?? null;

        if (!is_array($recipients)) {
            throw new \InvalidArgumentException('Les "email_settings" de la réponse Pennylane doivent contenir un tableau "recipients".');
        }

        if (null !== $billingEmailTemplate && (!is_array($billingEmailTemplate) || !is_int($billingEmailTemplate['id'] ?? null) || !is_string($billingEmailTemplate['label'] ?? null))) {
            throw new \InvalidArgumentException('Le "billing_email_template" de la réponse Pennylane doit être un objet avec un "id" entier et un "label" textuel, ou nul.');
        }

        return new self(
            recipients: array_values(array_filter($recipients, static fn (mixed $recipient): bool => is_string($recipient))),
            billingEmailTemplateId: is_array($billingEmailTemplate) ? $billingEmailTemplate['id'] : null,
            billingEmailTemplateLabel: is_array($billingEmailTemplate) ? $billingEmailTemplate['label'] : null,
        );
    }
}
