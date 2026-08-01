<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Filter;

final readonly class Sort implements \Stringable
{
    private function __construct(
        private string $field,
        private bool $descending,
    ) {
    }

    public static function asc(string $field): self
    {
        return new self($field, false);
    }

    public static function desc(string $field): self
    {
        return new self($field, true);
    }

    public function __toString(): string
    {
        return $this->descending ? '-'.$this->field : $this->field;
    }
}
