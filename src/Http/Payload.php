<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

/**
 * Construit le corps JSON d'une requête : omet ce qui n'a pas été fourni,
 * envoie un `null` explicite pour ce qui doit être vidé, et déballe les valeurs
 * que l'API attend sous forme scalaire ou tabulaire.
 */
final class Payload
{
    /**
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    public static function build(array $fields): array
    {
        $body = [];

        foreach ($fields as $key => $value) {
            if (null === $value) {
                continue;
            }

            $body[$key] = self::normalise($value);
        }

        return $body;
    }

    private static function normalise(mixed $value): mixed
    {
        if ($value instanceof Clear) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return $value;
    }
}
