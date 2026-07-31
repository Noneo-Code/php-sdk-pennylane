<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

use Symfony\Component\Yaml\Yaml;

final class SpecOperationsReader
{
    private const HTTP_METHODS = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'];

    /**
     * @return list<SpecOperation>
     */
    public static function fromYamlFile(string $path): array
    {
        /** @var array<string, mixed> $spec */
        $spec = Yaml::parseFile($path);
        /** @var array<string, array<string, array<string, mixed>>> $paths */
        $paths = $spec['paths'] ?? [];

        $operations = [];

        foreach ($paths as $operationPath => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (!in_array($method, self::HTTP_METHODS, true)) {
                    continue;
                }

                $operationId = $operation['operationId'];
                if (!is_string($operationId)) {
                    throw new \RuntimeException("operationId manquant ou invalide pour {$method} {$operationPath}.");
                }

                $operations[] = new SpecOperation(
                    strtoupper($method),
                    $operationPath,
                    $operationId,
                );
            }
        }

        return $operations;
    }
}
