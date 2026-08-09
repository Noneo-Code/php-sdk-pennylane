<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

/**
 * Refuse tout `float` sortant vers Pennylane.
 *
 * L'API attend ses montants en chaînes décimales (`"120.50"`). Un `float` PHP
 * ne représente pas exactement un décimal : selon la valeur et le réglage de
 * `serialize_precision`, `json_encode(120.50)` peut écrire `120.5`, et
 * `json_encode(0.1 + 0.2)` écrit `0.30000000000000004`. Une écriture comptable
 * fausse d'un centime est le pire défaut possible pour ce SDK — silencieuse et
 * comptablement fausse. On lève donc plutôt que d'envoyer.
 *
 * La règle n'a **aucune exception** : `2.0` est un float comme un autre, y
 * compris pour une quantité. Une règle sans exception est la seule qui
 * s'explique, et la seule qui ne laisse pas de trou.
 */
final class FloatGuard
{
    /**
     * @param array<array-key, mixed> $values
     *
     * @throws \InvalidArgumentException dès qu'une valeur, à n'importe quelle profondeur, est un float
     */
    public static function assertNoFloat(array $values, string $path = ''): void
    {
        foreach ($values as $key => $value) {
            $keyPath = '' === $path ? (string) $key : $path.'['.$key.']';

            if (is_float($value)) {
                throw new \InvalidArgumentException(self::message($keyPath, $value));
            }

            if (is_array($value)) {
                self::assertNoFloat($value, $keyPath);
            }
        }
    }

    private static function message(string $path, float $value): string
    {
        return sprintf(
            'Le champ « %s » a reçu le float %s. Pennylane attend les montants en chaîne décimale : '
            ."écrivez '120.50' plutôt que 120.50 — un float ne représente pas exactement un décimal "
            .'et fausserait le montant envoyé.',
            $path,
            var_export($value, true),
        );
    }
}
