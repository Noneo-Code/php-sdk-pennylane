<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

final class LlmsIndexParser
{
    /**
     * @return list<string>
     */
    public static function extractReferenceUrls(string $llmsTxt): array
    {
        preg_match_all('#\(([^\s()]+/reference/[^\s()]+\.md)\)#', $llmsTxt, $matches);

        return array_values(array_unique($matches[1]));
    }
}
