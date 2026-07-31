<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

use Symfony\Component\Yaml\Yaml;

final class YamlSpecWriter
{
    /**
     * @param array<string, mixed> $spec
     */
    public static function toYaml(array $spec): string
    {
        return Yaml::dump($spec, 10, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
}
