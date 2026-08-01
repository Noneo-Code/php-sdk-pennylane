<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PurchaseRequest;

final readonly class PurchaseOrderDto
{
    public function __construct(
        public string $filename,
        public string $url,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $filename = $body['filename'] ?? null;
        $url = $body['url'] ?? null;

        if (!is_string($filename)) {
            throw new \InvalidArgumentException('Le "purchase_order" de la réponse Pennylane doit contenir un "filename" textuel.');
        }

        if (!is_string($url)) {
            throw new \InvalidArgumentException('Le "purchase_order" de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        return new self($filename, $url);
    }
}
