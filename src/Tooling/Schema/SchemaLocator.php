<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecSources;

/**
 * Retrouve, dans les specs OpenAPI committées, le schéma auquel une fixture
 * prétend correspondre : celui d'une opération, éventuellement descendu
 * jusqu'à une propriété.
 *
 * Les deux specs officielles n'utilisent aucun `$ref` — chaque schéma est
 * écrit en toutes lettres là où il sert. Il n'y a donc rien à déréférencer,
 * seulement à traverser.
 */
final class SchemaLocator
{
    /**
     * @param list<mixed[]> $specs
     */
    public function __construct(private readonly array $specs)
    {
    }

    /**
     * Charge les specs committées à la racine du dépôt, celles-là mêmes que la
     * veille hebdomadaire surveille.
     */
    public static function fromRepository(string $rootDir): self
    {
        $specs = [];

        foreach (array_keys(SpecSources::URLS) as $file) {
            $path = rtrim($rootDir, '/').'/'.$file;
            $contents = file_get_contents($path);

            if (!is_string($contents)) {
                throw new \RuntimeException("Spec introuvable : {$path}. Lancez d'abord php bin/fetch-openapi.php.");
            }

            $spec = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($spec)) {
                throw new \RuntimeException("Spec JSON illisible : {$path}.");
            }

            $specs[] = $spec;
        }

        return new self($specs);
    }

    /**
     * @param string $pointer chemin de propriétés séparées par des points, par
     *                        exemple `items` ou `ledger_account`. Vide pour le
     *                        schéma de l'opération elle-même.
     *
     * @return mixed[]
     */
    public function locate(string $operationId, SchemaSource $source, string $pointer = ''): array
    {
        $schema = $this->operationSchema($operationId, $source);

        foreach (array_filter(explode('.', $pointer)) as $segment) {
            $current = self::crossArrays($schema);

            $child = str_contains($segment, '=')
                ? self::branchWhere($current, ...explode('=', $segment, 2))
                : self::property($current, $segment);

            if (null === $child) {
                throw new \RuntimeException(sprintf('Chemin "%s" introuvable dans le schéma de "%s".', $pointer, $operationId));
            }

            $schema = self::crossArrays($child);
        }

        return $schema;
    }

    /**
     * Un tableau se traverse sans qu'on le demande : une fixture décrit
     * toujours un élément, jamais la collection qui le porte. Seul un pointeur
     * traverse — sans lui, le schéma d'une opération de liste reste celui de
     * la page entière.
     *
     * @param mixed[] $schema
     *
     * @return mixed[]
     */
    private static function crossArrays(array $schema): array
    {
        while (is_array($items = $schema['items'] ?? null)) {
            $schema = $items;
        }

        return $schema;
    }

    /**
     * @return mixed[]
     */
    private function operationSchema(string $operationId, SchemaSource $source): array
    {
        $operation = $this->findOperation($operationId);

        $schema = match ($source) {
            SchemaSource::Request => self::contentSchema($operation['requestBody'] ?? null),
            SchemaSource::Response => self::successResponseSchema($operation['responses'] ?? null),
        };

        if (null === $schema) {
            throw new \RuntimeException(sprintf('L\'opération "%s" n\'a pas de %s documenté.', $operationId, SchemaSource::Request === $source ? 'corps de requête' : 'corps de réponse'));
        }

        return $schema;
    }

    /**
     * @return mixed[]
     */
    private function findOperation(string $operationId): array
    {
        foreach ($this->specs as $spec) {
            foreach ((array) ($spec['paths'] ?? []) as $pathItem) {
                foreach ((array) $pathItem as $operation) {
                    if (is_array($operation) && ($operation['operationId'] ?? null) === $operationId) {
                        return $operation;
                    }
                }
            }
        }

        throw new \RuntimeException(sprintf('Opération "%s" absente des specs OpenAPI.', $operationId));
    }

    /**
     * @return mixed[]|null
     */
    private static function successResponseSchema(mixed $responses): ?array
    {
        if (!is_array($responses)) {
            return null;
        }

        foreach ($responses as $status => $response) {
            if (1 === preg_match('/^2\d\d$/', (string) $status) && null !== $schema = self::contentSchema($response)) {
                return $schema;
            }
        }

        return null;
    }

    /**
     * Le corps structuré, quel que soit le libellé exact du type de contenu —
     * la spec écrit parfois `application/json; charset=utf-8`. Les envois de
     * fichiers passent en `multipart/form-data` : leur schéma décrit les
     * champs du formulaire, ce qui se confronte aussi bien.
     *
     * @return mixed[]|null
     */
    private static function contentSchema(mixed $body): ?array
    {
        if (!is_array($body) || !is_array($content = $body['content'] ?? null)) {
            return null;
        }

        foreach ($content as $mediaType => $media) {
            $mediaType = (string) $mediaType;

            if (!str_contains($mediaType, 'json') && !str_contains($mediaType, 'multipart/form-data')) {
                continue;
            }

            if (is_array($media) && is_array($media['schema'] ?? null)) {
                $schema = $media['schema'];

                return $schema;
            }
        }

        return null;
    }

    /**
     * La branche d'un combinateur qui porte un discriminant donné.
     *
     * Pennylane écrit ses `oneOf` en répétant la même propriété — `status`,
     * `customer_type` — avec une énumération à une seule valeur par branche.
     * Désigner la branche par ce discriminant plutôt que par son rang met
     * l'annotation à l'abri d'une réécriture de spec qui les réordonnerait.
     *
     * @param mixed[] $schema
     *
     * @return mixed[]|null
     */
    private static function branchWhere(array $schema, string $property, string $value): ?array
    {
        foreach (['oneOf', 'anyOf', 'allOf'] as $keyword) {
            foreach ((array) ($schema[$keyword] ?? []) as $branch) {
                if (!is_array($branch)) {
                    continue;
                }

                $discriminant = self::property($branch, $property);

                if (null !== $discriminant && in_array($value, (array) ($discriminant['enum'] ?? []), true)) {
                    return $branch;
                }
            }
        }

        return null;
    }

    /**
     * Descend d'un cran. Les branches d'un combinateur se fouillent l'une
     * après l'autre — un `oneOf` entre client entreprise et client particulier
     * ne doit pas cacher l'adresse de facturation que les deux portent.
     *
     * @param mixed[] $schema
     *
     * @return mixed[]|null
     */
    private static function property(array $schema, string $name): ?array
    {
        if (is_array($properties = $schema['properties'] ?? null) && is_array($properties[$name] ?? null)) {
            $property = $properties[$name];

            return $property;
        }

        foreach (['allOf', 'oneOf', 'anyOf'] as $keyword) {
            foreach ((array) ($schema[$keyword] ?? []) as $branch) {
                if (!is_array($branch)) {
                    continue;
                }

                if (null !== $found = self::property($branch, $name)) {
                    return $found;
                }
            }
        }

        return null;
    }
}
