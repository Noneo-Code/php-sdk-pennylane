<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

/**
 * Réécrit la spec téléchargée en JSON indenté. Pennylane la publie sur une seule
 * ligne de deux méga-octets : committée telle quelle, elle rendrait tout diff
 * illisible et toute lecture ciblée impossible.
 */
final class SpecJsonWriter
{
    /**
     * @param array<string, mixed> $spec
     */
    public static function toJson(array $spec): string
    {
        return json_encode(
            $spec,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        )."\n";
    }
}
