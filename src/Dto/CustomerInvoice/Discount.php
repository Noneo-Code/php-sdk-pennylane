<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class Discount
{
    public function __construct(
        public DiscountType $type,
        public ?string $value,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $type = $body['type'] ?? null;
        $value = $body['value'] ?? null;

        if (!is_string($type)) {
            throw new \InvalidArgumentException('Le "discount" de la réponse Pennylane doit contenir un "type" textuel.');
        }

        if (null !== $value && !is_string($value)) {
            throw new \InvalidArgumentException('Le "discount" de la réponse Pennylane doit contenir une "value" textuelle ou nulle.');
        }

        return new self(DiscountType::from($type), $value);
    }

    /** @return array{type: string, value: ?string} */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'value' => $this->value,
        ];
    }
}
