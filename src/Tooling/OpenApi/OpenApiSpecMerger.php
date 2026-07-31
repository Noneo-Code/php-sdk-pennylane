<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

final class OpenApiSpecMerger
{
    private const METHOD_ORDER = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'];

    private ?string $openapi = null;

    /** @var array<string, mixed>|null */
    private ?array $info = null;

    /** @var list<array<string, mixed>>|null */
    private ?array $servers = null;

    /** @var array<string, array<string, array<string, mixed>>> */
    private array $paths = [];

    /** @var array<string, array<string, array<string, string>>> */
    private array $securityScopes = [];

    /** @var array<string, array<string, mixed>> */
    private array $securitySchemes = [];

    /**
     * @param array<string, mixed> $fragment
     */
    public function addFragment(array $fragment): void
    {
        if (null === $this->openapi) {
            $openapi = $fragment['openapi'];
            if (!is_string($openapi)) {
                throw new \RuntimeException('Le champ "openapi" du fragment doit être une chaîne.');
            }
            $this->openapi = $openapi;
        }

        if (null === $this->info) {
            /** @var array<string, mixed> $info */
            $info = $fragment['info'];
            $this->info = $info;
        }

        if (null === $this->servers) {
            /** @var list<array<string, mixed>> $servers */
            $servers = $fragment['servers'];
            $this->servers = $servers;
        }

        /** @var array<string, array<string, array<string, mixed>>> $paths */
        $paths = $fragment['paths'];

        foreach ($paths as $path => $operations) {
            foreach ($operations as $method => $operation) {
                $this->addOperation($path, $method, $operation);
            }
        }

        /** @var array<string, mixed> $components */
        $components = $fragment['components'] ?? [];
        /** @var array<string, array<string, mixed>> $securitySchemes */
        $securitySchemes = $components['securitySchemes'] ?? [];

        foreach ($securitySchemes as $schemeName => $scheme) {
            $this->mergeSecurityScheme($schemeName, $scheme);
        }
    }

    /**
     * @param array<string, mixed> $operation
     */
    private function addOperation(string $path, string $method, array $operation): void
    {
        $existing = $this->paths[$path][$method] ?? null;

        if (null !== $existing && $existing !== $operation) {
            throw new \RuntimeException("Opération en conflit pour {$method} {$path}.");
        }

        $this->paths[$path][$method] = $operation;
    }

    /**
     * @param array<string, mixed> $scheme
     */
    private function mergeSecurityScheme(string $schemeName, array $scheme): void
    {
        /** @var array<string, array<string, mixed>> $flows */
        $flows = $scheme['flows'] ?? [];

        foreach ($flows as $flowName => $flow) {
            /** @var array<string, string> $scopes */
            $scopes = $flow['scopes'] ?? [];

            foreach ($scopes as $scope => $description) {
                $this->securityScopes[$schemeName][$flowName] ??= [];
                $this->securityScopes[$schemeName][$flowName][$scope] = $description;
            }
        }

        $this->securitySchemes[$schemeName] ??= $scheme;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $paths = $this->paths;
        ksort($paths);

        foreach ($paths as $path => $operations) {
            uksort($operations, static fn (string $a, string $b): int => array_search($a, self::METHOD_ORDER, true) <=> array_search($b, self::METHOD_ORDER, true));
            $paths[$path] = $operations;
        }

        $securitySchemes = $this->securitySchemes;

        foreach ($securitySchemes as $schemeName => $scheme) {
            /** @var array<string, array<string, mixed>> $flows */
            $flows = $scheme['flows'] ?? [];

            foreach ($flows as $flowName => $flow) {
                $scopes = $this->securityScopes[$schemeName][$flowName] ?? [];
                ksort($scopes);
                $flow['scopes'] = $scopes;
                $flows[$flowName] = $flow;
            }

            $scheme['flows'] = $flows;
            $securitySchemes[$schemeName] = $scheme;
        }

        return [
            'openapi' => $this->openapi,
            'info' => $this->info,
            'servers' => $this->servers,
            'paths' => $paths,
            'components' => [
                'securitySchemes' => $securitySchemes,
            ],
        ];
    }
}
