<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

final readonly class NewRecurringRule
{
    private function __construct(
        public RecurringRuleType $type,
        public ?int $interval = null,
        public ?int $count = null,
        public ?int $dayOfMonth = null,
        public ?DayOfWeek $dayOfWeek = null,
    ) {
    }

    public static function yearly(?int $interval = null, ?int $count = null): self
    {
        return new self(RecurringRuleType::Yearly, $interval, $count);
    }

    public static function monthly(?int $interval = null, ?int $count = null, ?int $dayOfMonth = null): self
    {
        return new self(RecurringRuleType::Monthly, $interval, $count, dayOfMonth: $dayOfMonth);
    }

    public static function weekly(?int $interval = null, ?int $count = null, ?DayOfWeek $dayOfWeek = null): self
    {
        return new self(RecurringRuleType::Weekly, $interval, $count, dayOfWeek: $dayOfWeek);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'interval' => $this->interval,
            'count' => $this->count,
            'day_of_month' => $this->dayOfMonth,
            'day_of_week' => $this->dayOfWeek?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
