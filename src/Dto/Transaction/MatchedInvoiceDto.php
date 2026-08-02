<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Transaction;

final readonly class MatchedInvoiceDto
{
    public function __construct(
        public int $id,
        public InvoiceType $type,
        public string $url,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $type = $body['type'] ?? null;
        $url = $body['url'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('La facture rapprochée de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($type)) {
            throw new \InvalidArgumentException('La facture rapprochée de la réponse Pennylane doit contenir un "type" textuel.');
        }

        if (!is_string($url)) {
            throw new \InvalidArgumentException('La facture rapprochée de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        return new self(
            id: $id,
            type: InvoiceType::from($type),
            url: $url,
        );
    }
}
