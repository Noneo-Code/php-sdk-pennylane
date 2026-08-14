<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Traduction exécutable de la définition de « fini » du ticket #29 : un
 * consommateur instancie, utilise et teste le SDK sans rencontrer un seul
 * symbole `Saloon\*`. Toute classe hors namespaces internes est inspectée par
 * réflexion — parent, interfaces, signatures et propriétés publiques.
 */
final class PublicApiSurfaceTest extends TestCase
{
    /**
     * Namespaces internes par convention : jamais présentés comme API publique.
     */
    private const array INTERNAL_NAMESPACE_PARTS = [
        'Noneo\\Sdk\\Pennylane\\Requests\\',
        'Noneo\\Sdk\\Pennylane\\Resource\\',
        'Noneo\\Sdk\\Pennylane\\Concerns\\',
        'Noneo\\Sdk\\Pennylane\\Http\\Connector\\',
    ];

    public function testNoPublicSignatureExposesASaloonType(): void
    {
        $offenders = [];

        foreach ($this->publicClasses() as $reflection) {
            $parent = $reflection->getParentClass();

            if ($parent instanceof \ReflectionClass && str_starts_with($parent->getName(), 'Saloon\\')) {
                $offenders[] = sprintf('%s extends %s', $reflection->getName(), $parent->getName());
            }

            foreach ($reflection->getInterfaceNames() as $interface) {
                if (str_starts_with($interface, 'Saloon\\')) {
                    $offenders[] = sprintf('%s implements %s', $reflection->getName(), $interface);
                }
            }

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                    continue;
                }

                if (str_contains((string) $method->getDocComment(), '@internal')) {
                    continue;
                }

                foreach ($this->saloonTypesIn($method) as $type) {
                    $offenders[] = sprintf('%s::%s() expose %s', $reflection->getName(), $method->getName(), $type);
                }
            }

            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                foreach ($this->saloonNamesIn($property->getType()) as $name) {
                    $offenders[] = sprintf('%s::$%s expose %s', $reflection->getName(), $property->getName(), $name);
                }
            }
        }

        self::assertSame([], $offenders, sprintf(
            "L'API publique expose des types Saloon :\n- %s",
            implode("\n- ", $offenders),
        ));
    }

    /**
     * @return \Generator<\ReflectionClass<object>>
     */
    private function publicClasses(): \Generator
    {
        $sourceDirectory = dirname(__DIR__).'/src';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDirectory));

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || 'php' !== $file->getExtension()) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($sourceDirectory) + 1, -strlen('.php'));
            $class = 'Noneo\\Sdk\\Pennylane\\'.str_replace('/', '\\', (string) $relative);

            foreach (self::INTERNAL_NAMESPACE_PARTS as $internal) {
                if (str_starts_with($class, $internal)) {
                    continue 2;
                }
            }

            if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            if (str_contains((string) $reflection->getDocComment(), '@internal')) {
                continue;
            }

            yield $reflection;
        }
    }

    /**
     * @return list<string>
     */
    private function saloonTypesIn(\ReflectionMethod $method): array
    {
        $names = [];

        foreach ($method->getParameters() as $parameter) {
            $names = [...$names, ...$this->saloonNamesIn($parameter->getType())];
        }

        return [...$names, ...$this->saloonNamesIn($method->getReturnType())];
    }

    /**
     * @return list<string>
     */
    private function saloonNamesIn(?\ReflectionType $type): array
    {
        if ($type instanceof \ReflectionNamedType) {
            return str_starts_with($type->getName(), 'Saloon\\') ? [$type->getName()] : [];
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            return array_merge(...array_map(
                fn (\ReflectionType $inner): array => $this->saloonNamesIn($inner),
                $type->getTypes(),
            ));
        }

        return [];
    }
}
