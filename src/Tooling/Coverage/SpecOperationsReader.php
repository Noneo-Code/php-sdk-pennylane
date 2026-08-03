<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

final class SpecOperationsReader
{
    private const HTTP_METHODS = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'];

    /**
     * @return list<SpecOperation>
     */
    public static function fromJsonFile(string $path): array
    {
        $contents = file_get_contents($path);

        if (!is_string($contents)) {
            throw new \RuntimeException("Spec introuvable : {$path}");
        }

        try {
            /** @var array<string, mixed> $spec */
            $spec = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Spec JSON illisible : {$path}", 0, $e);
        }

        return self::fromArray($spec);
    }

    /**
     * @param array<string, mixed> $spec
     *
     * @return list<SpecOperation>
     */
    public static function fromArray(array $spec): array
    {
        /** @var array<string, array<string, array<string, mixed>>> $paths */
        $paths = $spec['paths'] ?? [];

        $operations = [];

        foreach ($paths as $operationPath => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (!in_array($method, self::HTTP_METHODS, true)) {
                    continue;
                }

                $operationId = $operation['operationId'] ?? null;
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
