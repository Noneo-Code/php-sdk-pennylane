<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Récolte tout ce qui porte un `#[DocumentedBy]` : les fixtures des tests d'un
 * côté, les enums du SDK de l'autre.
 *
 * La découverte suit la convention PSR-4 du dépôt — un fichier, une classe,
 * dont le nom est celui du fichier. Les classes sont ensuite lues par
 * réflexion : c'est le seul moyen de rapporter la *valeur* d'une fixture, pas
 * seulement son texte.
 */
final class TargetScanner
{
    /**
     * Convention du dépôt : une fixture s'appelle `documentedPayload()`, ou
     * `documented…Payload()` quand une classe en porte plusieurs. Le préfixe
     * distingue la fixture des méthodes de test qui la consomment.
     */
    private const FIXTURE_PREFIX = 'documented';

    /**
     * @param list<class-string> $classes
     *
     * @return list<PayloadTarget>
     */
    public static function payloadsIn(array $classes): array
    {
        $targets = [];

        foreach ($classes as $class) {
            foreach ((new \ReflectionClass($class))->getMethods() as $method) {
                $attribute = $method->getAttributes(DocumentedBy::class)[0] ?? null;

                if (null === $attribute) {
                    continue;
                }

                $subject = $class.'::'.$method->getName();

                if (!$method->isStatic() || !$method->isPublic()) {
                    throw new \RuntimeException($subject.' : une fixture annotée doit être publique et statique.');
                }

                $targets[] = new PayloadTarget($subject, $attribute->newInstance(), $method->invoke(null));
            }
        }

        return $targets;
    }

    /**
     * Les méthodes qui *ressemblent* à une fixture sans porter d'annotation.
     * Sans ce filet, oublier un `#[DocumentedBy]` sur une nouvelle fixture la
     * ferait sortir du contrôle en silence — le contraire de ce que l'outil
     * cherche à empêcher.
     *
     * @param list<class-string> $classes
     *
     * @return list<string>
     */
    public static function unannotatedPayloadsIn(array $classes): array
    {
        $orphans = [];

        foreach ($classes as $class) {
            foreach ((new \ReflectionClass($class))->getMethods() as $method) {
                if (!str_starts_with($method->getName(), self::FIXTURE_PREFIX)) {
                    continue;
                }

                if ([] === $method->getAttributes(DocumentedBy::class)) {
                    $orphans[] = $class.'::'.$method->getName();
                }
            }
        }

        sort($orphans);

        return $orphans;
    }

    /**
     * @param list<class-string> $classes
     *
     * @return list<EnumTarget>
     */
    public static function enumsIn(array $classes): array
    {
        $targets = [];

        foreach ($classes as $class) {
            // Le scan reçoit tout un dossier, DTO compris : ce qui n'est pas
            // un enum n'est simplement pas une cible.
            if (!enum_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionEnum($class);
            $attribute = $reflection->getAttributes(DocumentedBy::class)[0] ?? null;

            if (null === $attribute) {
                continue;
            }

            $values = [];

            foreach ($reflection->getCases() as $case) {
                // Un enum pur n'a rien à comparer à une énumération de la spec :
                // ses cas n'ont pas de valeur transmise sur le fil.
                if (!$case instanceof \ReflectionEnumBackedCase) {
                    continue;
                }

                $value = $case->getBackingValue();
                $values[] = (string) $value;
            }

            $targets[] = new EnumTarget($class, $attribute->newInstance(), $values);
        }

        return $targets;
    }

    /**
     * Les classes déclarées sous `$directory`, en supposant la correspondance
     * PSR-4 entre l'arborescence et `$namespacePrefix`. Ce qui n'existe pas à
     * l'exécution — un fichier qui déclare autre chose qu'une classe — est
     * ignoré plutôt que signalé : ce n'est pas une cible.
     *
     * @return list<class-string>
     */
    public static function classesIn(string $directory, string $namespacePrefix): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        $classes = [];
        $root = rtrim($directory, '/');

        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);

            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($root) + 1);
            $class = rtrim($namespacePrefix, '\\').'\\'.str_replace('/', '\\', substr($relative, 0, -4));

            if (class_exists($class) || enum_exists($class)) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }
}
