<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

final readonly class NewMode
{
    /** @param ?list<string> $emailRecipients */
    private function __construct(
        public BillingSubscriptionMode $type,
        public ?array $emailRecipients = null,
    ) {
    }

    /** @param list<string> $recipients */
    public static function email(array $recipients = []): self
    {
        return new self(BillingSubscriptionMode::Email, $recipients);
    }

    public static function awaitingValidation(): self
    {
        return new self(BillingSubscriptionMode::AwaitingValidation);
    }

    public static function finalized(): self
    {
        return new self(BillingSubscriptionMode::Finalized);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        if (BillingSubscriptionMode::Email !== $this->type) {
            return ['type' => $this->type->value];
        }

        return [
            'type' => $this->type->value,
            'email_settings' => array_filter([
                'recipients' => [] !== ($this->emailRecipients ?? []) ? $this->emailRecipients : null,
            ], static fn (mixed $value): bool => null !== $value),
        ];
    }
}
