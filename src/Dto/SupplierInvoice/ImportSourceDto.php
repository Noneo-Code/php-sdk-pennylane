<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

final readonly class ImportSourceDto
{
    public function __construct(
        public ImportSourceConnector $source,
        public string $path,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $source = $body['source'] ?? null;
        $path = $body['path'] ?? null;

        if (!is_string($source)) {
            throw new \InvalidArgumentException('L\'"import_source" de la réponse Pennylane doit contenir une "source" textuelle.');
        }

        if (!is_string($path)) {
            throw new \InvalidArgumentException('L\'"import_source" de la réponse Pennylane doit contenir un "path" textuel.');
        }

        return new self(ImportSourceConnector::from($source), $path);
    }
}
