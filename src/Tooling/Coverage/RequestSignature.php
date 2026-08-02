<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

/**
 * Ce qu'une classe `Request` envoie réellement : sa méthode HTTP et son
 * endpoint, paramètres de chemin normalisés en `{}`.
 */
final class RequestSignature
{
    public function __construct(
        public readonly string $basename,
        public readonly string $method,
        public readonly string $endpoint,
    ) {
    }
}
