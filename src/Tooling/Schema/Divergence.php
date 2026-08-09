<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Ce qui sépare une cible du schéma dont elle se réclame.
 */
final class Divergence
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public readonly string $subject,
        public readonly array $errors,
    ) {
    }
}
