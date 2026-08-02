<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

/**
 * Marqueur d'effacement pour les mises à jour partielles.
 *
 * Un champ laissé à `null` n'est pas envoyé — Pennylane conserve donc sa valeur
 * courante. Passer `Clear::value()` envoie un `null` explicite, seule façon de
 * vider un champ optionnel.
 *
 * ```php
 * $pennylane->suppliers()->update(
 *     id: 42,
 *     notes: Clear::value(), // "notes": null — le champ est vidé
 *     phone: null,           // absent du corps — le téléphone est conservé
 * );
 * ```
 *
 * (`Unset` aurait été le nom naturel, mais c'est un mot réservé du langage.)
 */
final class Clear
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function value(): self
    {
        return self::$instance ??= new self();
    }
}
