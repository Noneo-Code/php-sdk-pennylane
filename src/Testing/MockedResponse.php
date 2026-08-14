<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Testing;

/**
 * Réponse simulée, dans le vocabulaire du SDK : le consommateur ne touche
 * jamais `Saloon\Http\Faking\MockResponse`.
 */
final readonly class MockedResponse
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        public int $status,
        public array $body,
    ) {
    }
}
