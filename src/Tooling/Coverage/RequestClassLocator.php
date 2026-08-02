<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

/**
 * Lit les classes `Request` du SDK sans les charger : leurs constructeurs
 * exigent des arguments métier et `resolveEndpoint()` n'a de sens que sur une
 * instance. Une lecture statique du fichier suffit à en tirer la méthode HTTP
 * et le chemin réellement appelés.
 */
final class RequestClassLocator
{
    /**
     * @return list<RequestSignature>
     */
    public static function signatures(string $requestsDir): array
    {
        if (!is_dir($requestsDir)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($requestsDir, \FilesystemIterator::SKIP_DOTS),
        );

        $signatures = [];

        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);

            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            if (false === $source) {
                continue;
            }

            $signatures[] = new RequestSignature(
                $file->getBasename('.php'),
                self::readMethod($source),
                self::readEndpoint($source),
            );
        }

        usort($signatures, static fn (RequestSignature $a, RequestSignature $b): int => $a->basename <=> $b->basename);

        return $signatures;
    }

    private static function readMethod(string $source): string
    {
        if (1 === preg_match('/Method::(\w+)/', $source, $matches)) {
            return strtoupper($matches[1]);
        }

        return '';
    }

    private static function readEndpoint(string $source): string
    {
        if (1 !== preg_match('/function resolveEndpoint\(\): string\s*\{(.*?)\n    \}/s', $source, $body)) {
            return '';
        }

        if (1 !== preg_match('/return\s+(.+?);/s', $body[1], $matches)) {
            return '';
        }

        $endpoint = trim($matches[1], "'\" \t\n");

        // "/supplier_invoices/{$this->id}/categories"
        $endpoint = preg_replace('/\{\$this->\w+(\??->\w+)*\}/', '{}', $endpoint) ?? $endpoint;
        // '/customers/'.$this->id.'/contacts' et '/customers/'.$this->id
        $endpoint = preg_replace('/\'\s*\.\s*\$this->[\w>?-]+\s*\.\s*\'/', '{}', $endpoint) ?? $endpoint;
        $endpoint = preg_replace('/\'\s*\.\s*\$this->[\w>?-]+$/', '{}', $endpoint) ?? $endpoint;

        return rtrim($endpoint, '/');
    }
}
