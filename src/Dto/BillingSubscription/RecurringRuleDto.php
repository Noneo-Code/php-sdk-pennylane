<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

final readonly class RecurringRuleDto
{
    /**
     * @param ?list<int> $dayOfMonth
     * @param ?list<int> $monthOfYear
     * @param ?list<int> $day
     */
    public function __construct(
        public ?array $dayOfMonth,
        public ?array $monthOfYear,
        public ?int $weekStart,
        public ?array $day,
        public RecurringRuleType $ruleType,
        public ?int $interval,
        public ?int $count,
        public ?string $until,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $ruleType = $body['rule_type'] ?? null;

        if (!array_key_exists('day_of_month', $body) || !array_key_exists('month_of_year', $body) || !array_key_exists('week_start', $body) || !array_key_exists('day', $body)) {
            throw new \InvalidArgumentException('La "recurring_rule" de la réponse Pennylane doit contenir "day_of_month", "month_of_year", "week_start" et "day".');
        }

        if (!is_string($ruleType)) {
            throw new \InvalidArgumentException('La "recurring_rule" de la réponse Pennylane doit contenir un "rule_type" textuel.');
        }

        return new self(
            dayOfMonth: self::intList($body['day_of_month']),
            monthOfYear: self::intList($body['month_of_year']),
            weekStart: is_int($body['week_start'] ?? null) ? $body['week_start'] : null,
            day: self::intList($body['day']),
            ruleType: RecurringRuleType::from($ruleType),
            interval: is_int($body['interval'] ?? null) ? $body['interval'] : null,
            count: is_int($body['count'] ?? null) ? $body['count'] : null,
            until: is_string($body['until'] ?? null) ? $body['until'] : null,
        );
    }

    /**
     * @return ?list<int>
     */
    private static function intList(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return array_values(array_filter($value, static fn (mixed $item): bool => is_int($item)));
    }
}
