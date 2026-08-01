<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class EInvoiceImportDto
{
    public function __construct(
        public int $id,
        public string $url,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $url = $body['url'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($url)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        return new self($id, $url);
    }
}
