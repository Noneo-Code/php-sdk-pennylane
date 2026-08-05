<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Confronte les cas d'un enum backed du SDK aux valeurs que la spec déclare
 * pour le champ correspondant.
 *
 * Les deux sens comptent. Une valeur de la spec absente du SDK est un incident
 * en puissance : l'API la rendra un jour, et le `from()` du DTO lèvera sur une
 * réponse pourtant valide. Une valeur du SDK absente de la spec est soit un
 * reliquat d'une version antérieure, soit une valeur recopiée de travers.
 */
final class EnumCoverage
{
    /**
     * @param list<string> $sdkValues valeurs des cas de l'enum PHP
     * @param mixed[]      $schema    schéma du champ, combinateurs compris
     *
     * @return list<string>
     */
    public static function compare(array $sdkValues, array $schema): array
    {
        $specValues = self::enumValues($schema);

        if (null === $specValues) {
            throw new \RuntimeException('Le schéma visé ne déclare aucune énumération.');
        }

        $errors = [];

        if ([] !== $missing = array_values(array_diff($specValues, $sdkValues))) {
            $errors[] = sprintf('valeurs de la spec absentes du SDK : %s.', implode(', ', $missing));
        }

        if ([] !== $extra = array_values(array_diff($sdkValues, $specValues))) {
            $errors[] = sprintf('valeurs du SDK absentes de la spec : %s.', implode(', ', $extra));
        }

        return $errors;
    }

    /**
     * L'énumération est rarement au premier niveau : la spec l'enveloppe dans
     * un `allOf` pour y accrocher une description, ou dans un `anyOf` avec un
     * `null` pour la rendre nullable.
     *
     * @param mixed[] $schema
     *
     * @return list<string>|null
     */
    private static function enumValues(array $schema): ?array
    {
        if (is_array($enum = $schema['enum'] ?? null) && [] !== $enum) {
            return array_values(array_map(static fn (mixed $value): string => (string) (is_scalar($value) ? $value : json_encode($value)), $enum));
        }

        foreach (['allOf', 'oneOf', 'anyOf'] as $keyword) {
            foreach ((array) ($schema[$keyword] ?? []) as $branch) {
                if (!is_array($branch)) {
                    continue;
                }

                if (null !== $values = self::enumValues($branch)) {
                    return $values;
                }
            }
        }

        return null;
    }
}
