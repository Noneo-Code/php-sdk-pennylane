<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

final class SpecOperation
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $operationId,
    ) {
    }
}
