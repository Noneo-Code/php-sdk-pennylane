<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Filter;

final readonly class Filter implements \JsonSerializable
{
    public function __construct(
        public string $field,
        public FilterOperator $operator,
        public mixed $value,
    ) {
    }

    /**
     * @return array{field: string, operator: string, value: mixed}
     */
    public function jsonSerialize(): array
    {
        return [
            'field' => $this->field,
            'operator' => $this->operator->value,
            'value' => $this->value,
        ];
    }
}
