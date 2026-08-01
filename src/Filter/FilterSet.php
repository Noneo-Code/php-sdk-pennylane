<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Filter;

final class FilterSet implements \JsonSerializable
{
    /** @var list<Filter> */
    private array $filters = [];

    public static function make(): self
    {
        return new self();
    }

    public function eq(string $field, mixed $value): static
    {
        return $this->add($field, FilterOperator::Eq, $value);
    }

    public function notEq(string $field, mixed $value): static
    {
        return $this->add($field, FilterOperator::NotEq, $value);
    }

    public function lt(string $field, mixed $value): static
    {
        return $this->add($field, FilterOperator::Lt, $value);
    }

    public function lteq(string $field, mixed $value): static
    {
        return $this->add($field, FilterOperator::Lteq, $value);
    }

    public function gt(string $field, mixed $value): static
    {
        return $this->add($field, FilterOperator::Gt, $value);
    }

    public function gteq(string $field, mixed $value): static
    {
        return $this->add($field, FilterOperator::Gteq, $value);
    }

    /** @param mixed[] $value */
    public function in(string $field, array $value): static
    {
        return $this->add($field, FilterOperator::In, $value);
    }

    /** @param mixed[] $value */
    public function notIn(string $field, array $value): static
    {
        return $this->add($field, FilterOperator::NotIn, $value);
    }

    public function startWith(string $field, string $value): static
    {
        return $this->add($field, FilterOperator::StartWith, $value);
    }

    public function add(string $field, FilterOperator $operator, mixed $value): static
    {
        $this->filters[] = new Filter($field, $operator, $value);

        return $this;
    }

    public function isEmpty(): bool
    {
        return [] === $this->filters;
    }

    /** @return list<array{field: string, operator: string, value: mixed}> */
    public function jsonSerialize(): array
    {
        return array_map(
            static fn (Filter $filter): array => $filter->jsonSerialize(),
            $this->filters,
        );
    }

    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
}
