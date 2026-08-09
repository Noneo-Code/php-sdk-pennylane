<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Une fixture de test annotée, et la valeur qu'elle rend.
 */
final class PayloadTarget
{
    public function __construct(
        public readonly string $subject,
        public readonly DocumentedBy $documentedBy,
        public readonly mixed $payload,
    ) {
    }
}
