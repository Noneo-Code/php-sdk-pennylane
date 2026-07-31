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
}
