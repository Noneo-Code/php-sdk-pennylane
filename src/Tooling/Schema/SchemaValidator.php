<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Confronte un payload décodé au sous-ensemble d'OpenAPI 3 que la spec
 * Pennylane utilise réellement : `type`, `properties`, `required`, `enum`,
 * `items`, `nullable`, et les combinateurs `allOf` / `oneOf` / `anyOf`. Les
 * deux specs committées ne contiennent aucun `$ref` — tout y est inline — donc
 * rien à résoudre ici.
 *
 * Le sens de la validation compte autant que les règles : les fixtures des
 * tests sont **recopiées à la main** depuis la documentation. Un champ qu'elles
 * portent et que le schéma ignore est le cas qui nous intéresse le plus — le
 * champ « probable » inventé de bonne foi. On le signale donc même quand le
 * schéma ne dit pas `additionalProperties: false`.
 */
final class SchemaValidator
{
    /**
     * @param mixed[] $schema
     *
     * @return list<string> les écarts, dans l'ordre de lecture du payload ;
     *                      tableau vide si le payload colle au schéma
     */
    public static function validate(mixed $payload, array $schema, string $path = '$'): array
    {
        if ([] !== $branches = self::branches($schema, 'oneOf', 'anyOf')) {
            return self::validateAlternatives($payload, $schema, $branches, $path);
        }

        $errors = [];

        foreach ((array) ($schema['allOf'] ?? []) as $branch) {
            if (!is_array($branch)) {
                continue;
            }

            array_push($errors, ...self::validate($payload, $branch, $path));
        }

        if (null !== $error = self::checkNull($payload, $schema, $path)) {
            return '' === $error ? $errors : [...$errors, $error];
        }

        if (null !== $error = self::checkType($payload, $schema, $path)) {
            return [...$errors, $error];
        }

        if (null !== $error = self::checkEnum($payload, $schema, $path)) {
            $errors[] = $error;
        }

        if (is_array($payload)) {
            array_push($errors, ...self::validateObject($payload, $schema, $path));
            array_push($errors, ...self::validateArrayItems($payload, $schema, $path));
        }

        return $errors;
    }

    /**
     * `oneOf` et `anyOf` valent, ici, la même chose : la fixture décrit une des
     * formes possibles. On rend les écarts de la branche la plus proche, faute
     * de quoi le rapport listerait les incompatibilités de toutes les autres —
     * illisible, et trompeur.
     *
     * @param mixed[]       $schema
     * @param list<mixed[]> $branches
     *
     * @return list<string>
     */
    private static function validateAlternatives(mixed $payload, array $schema, array $branches, string $path): array
    {
        $best = null;

        foreach ($branches as $branch) {
            // Une branche hérite des contraintes portées à côté du
            // combinateur — `nullable` en tête.
            $errors = self::validate($payload, [...array_diff_key($schema, array_flip(['oneOf', 'anyOf'])), ...$branch], $path);

            if ([] === $errors) {
                return [];
            }

            if (null === $best || count($errors) < count($best)) {
                $best = $errors;
            }
        }

        return $best ?? [];
    }

    /**
     * @param mixed[] $schema
     *
     * @return list<mixed[]>
     */
    private static function branches(array $schema, string ...$keywords): array
    {
        foreach ($keywords as $keyword) {
            if (!is_array($schema[$keyword] ?? null)) {
                continue;
            }

            $branches = array_values(array_filter($schema[$keyword], is_array(...)));

            if ([] !== $branches) {
                return $branches;
            }
        }

        return [];
    }

    /**
     * Rend `null` si la valeur n'est pas nulle, `''` si elle l'est légitimement
     * — auquel cas plus rien n'est à vérifier —, et le message d'écart sinon.
     *
     * @param mixed[] $schema
     */
    private static function checkNull(mixed $payload, array $schema, string $path): ?string
    {
        if (null !== $payload) {
            return null;
        }

        $nullable = true === ($schema['nullable'] ?? false)
            || in_array('null', (array) ($schema['type'] ?? []), true)
            || !isset($schema['type']);

        return $nullable ? '' : sprintf('%s : champ non nullable valué à null.', $path);
    }

    /**
     * @param mixed[] $schema
     */
    private static function checkType(mixed $payload, array $schema, string $path): ?string
    {
        $type = $schema['type'] ?? null;

        if (!is_string($type)) {
            return null;
        }

        $matches = match ($type) {
            'object' => is_array($payload) && (!array_is_list($payload) || [] === $payload),
            'array' => is_array($payload) && array_is_list($payload),
            'string' => is_string($payload),
            'integer' => is_int($payload),
            // Un entier est un nombre valide : JSON n'écrit pas `3.0`.
            'number' => is_int($payload) || is_float($payload),
            'boolean' => is_bool($payload),
            default => true,
        };

        if ($matches) {
            return null;
        }

        return sprintf('%s : %s attendu, %s reçu.', $path, self::describeType($type), self::describeValue($payload));
    }

    /**
     * @param mixed[] $schema
     */
    private static function checkEnum(mixed $payload, array $schema, string $path): ?string
    {
        $enum = $schema['enum'] ?? null;

        if (!is_array($enum) || [] === $enum || in_array($payload, $enum, true)) {
            return null;
        }

        return sprintf(
            '%s : valeur %s hors de l\'énumération (%s).',
            $path,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            implode(', ', array_map(static fn (mixed $case): string => is_scalar($case) ? (string) $case : (string) json_encode($case), $enum)),
        );
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $schema
     *
     * @return list<string>
     */
    private static function validateObject(array $payload, array $schema, string $path): array
    {
        $properties = $schema['properties'] ?? null;

        if (!is_array($properties)) {
            return [];
        }

        $errors = [];

        foreach ($properties as $name => $propertySchema) {
            if (!is_array($propertySchema) || !array_key_exists($name, $payload)) {
                continue;
            }

            array_push($errors, ...self::validate($payload[$name], $propertySchema, self::child($path, (string) $name)));
        }

        foreach ((array) ($schema['required'] ?? []) as $name) {
            if (is_string($name) && !array_key_exists($name, $payload)) {
                $errors[] = sprintf('%s : champ requis absent du payload.', self::child($path, $name));
            }
        }

        foreach (array_keys($payload) as $name) {
            if (!array_key_exists($name, $properties)) {
                $errors[] = sprintf('%s : champ absent du schéma.', self::child($path, (string) $name));
            }
        }

        return $errors;
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $schema
     *
     * @return list<string>
     */
    private static function validateArrayItems(array $payload, array $schema, string $path): array
    {
        $items = $schema['items'] ?? null;

        if (!is_array($items) || !array_is_list($payload)) {
            return [];
        }

        $errors = [];

        foreach ($payload as $index => $item) {
            array_push($errors, ...self::validate($item, $items, sprintf('%s[%d]', $path, $index)));
        }

        return $errors;
    }

    private static function child(string $path, string $name): string
    {
        return $path.'.'.$name;
    }

    private static function describeType(string $type): string
    {
        return match ($type) {
            'object' => 'objet',
            'array' => 'tableau',
            'string' => 'texte',
            'integer' => 'entier',
            'number' => 'nombre',
            'boolean' => 'booléen',
            default => $type,
        };
    }

    /**
     * Le type reçu se décrit dans le même vocabulaire que le type attendu :
     * « texte attendu, entier reçu » se lit, « texte attendu, int reçu » non.
     */
    private static function describeValue(mixed $value): string
    {
        if (is_array($value)) {
            return array_is_list($value) ? 'tableau' : 'objet';
        }

        return match (get_debug_type($value)) {
            'string' => 'texte',
            'int' => 'entier',
            'float' => 'nombre',
            'bool' => 'booléen',
            'null' => 'null',
            default => get_debug_type($value),
        };
    }
}
