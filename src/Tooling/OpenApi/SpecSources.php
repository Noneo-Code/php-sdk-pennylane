<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

/**
 * Les deux specs OpenAPI publiées par Pennylane, et le fichier committé qui leur
 * correspond à la racine du dépôt. Partagé par `bin/fetch-openapi.php` et
 * `bin/spec-watch.php` pour que la veille surveille exactement ce que le fetch
 * écrit.
 */
final class SpecSources
{
    /**
     * @var array<non-empty-string, non-empty-string> fichier committé => URL officielle
     */
    public const URLS = [
        'pennylane-api-v2.json' => 'https://pennylane.readme.io/openapi/accounting.json',
        'pennylane-api-firm-v1.json' => 'https://firm-pennylane.readme.io/openapi/referentials.json',
    ];
}
