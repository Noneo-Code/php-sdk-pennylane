<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

final class OpenApiSpecBuildResult
{
    /**
     * @param array<string, mixed> $spec
     * @param list<string>         $skippedUrls
     */
    public function __construct(
        public readonly array $spec,
        public readonly array $skippedUrls,
    ) {
    }

    /**
     * Nombre d'opérations HTTP écrites, tous chemins confondus.
     */
    public function operationCount(): int
    {
        $paths = $this->spec['paths'] ?? [];

        if (!is_array($paths)) {
            return 0;
        }

        $count = 0;

        foreach ($paths as $operations) {
            if (is_array($operations)) {
                $count += count($operations);
            }
        }

        return $count;
    }
}
