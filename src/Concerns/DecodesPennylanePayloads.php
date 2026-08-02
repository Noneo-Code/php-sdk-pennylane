<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

/**
 * Garde-fou commun aux ressources : un item de collection Pennylane doit être
 * un objet JSON avant d'être passé au `fromArray()` d'un DTO.
 */
trait DecodesPennylanePayloads
{
    /**
     * @return mixed[]
     */
    private static function asArray(mixed $item): array
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Un item de la réponse Pennylane doit être un objet.');
        }

        return $item;
    }
}
