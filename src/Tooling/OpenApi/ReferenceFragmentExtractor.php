<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

final class ReferenceFragmentExtractor
{
    /**
     * @return array<string, mixed>|null
     */
    public static function extract(string $markdown): ?array
    {
        if (1 !== preg_match('/```json\s*\n(.*?)\n```/s', $markdown, $matches)) {
            return null;
        }

        /** @var array<string, mixed> $fragment */
        $fragment = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        return $fragment;
    }
}
