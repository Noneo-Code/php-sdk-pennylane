<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

/**
 * Confronte les opérations de la spec aux requêtes du SDK sur ce qui compte —
 * la méthode HTTP et le chemin appelés. Un rapprochement par nom de classe
 * dépendrait de la convention de nommage des `operationId`, que Pennylane ne
 * tient pas (`company-fiscal-years`), et laisserait passer une classe bien
 * nommée qui tape la mauvaise URL.
 */
final class CoverageReport
{
    /**
     * @param list<SpecOperation>    $operations
     * @param list<RequestSignature> $signatures
     * @param array<string, string>  $excludedOperationIds operationId => raison de l'exclusion
     */
    public function __construct(
        private readonly array $operations,
        private readonly array $signatures,
        private readonly array $excludedOperationIds = [],
    ) {
    }

    public function total(): int
    {
        return count($this->includedOperations());
    }

    public function coveredCount(): int
    {
        return $this->total() - count($this->missing());
    }

    /**
     * @return list<SpecOperation>
     */
    public function missing(): array
    {
        return array_values(array_filter(
            $this->includedOperations(),
            fn (SpecOperation $operation): bool => !$this->isCovered($operation),
        ));
    }

    /**
     * Requêtes du SDK qui ne correspondent à aucune opération documentée :
     * chemin obsolète, faute de frappe, ou spec à rafraîchir.
     *
     * @return list<RequestSignature>
     */
    public function undocumented(): array
    {
        return array_values(array_filter(
            $this->signatures,
            function (RequestSignature $signature): bool {
                foreach ($this->operations as $operation) {
                    if ($this->matches($operation, $signature)) {
                        return false;
                    }
                }

                return true;
            },
        ));
    }

    /**
     * @return list<array{operation: SpecOperation, reason: string}>
     */
    public function excluded(): array
    {
        $excluded = [];

        foreach ($this->operations as $operation) {
            if (array_key_exists($operation->operationId, $this->excludedOperationIds)) {
                $excluded[] = ['operation' => $operation, 'reason' => $this->excludedOperationIds[$operation->operationId]];
            }
        }

        return $excluded;
    }

    private function isCovered(SpecOperation $operation): bool
    {
        foreach ($this->signatures as $signature) {
            if ($this->matches($operation, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function matches(SpecOperation $operation, RequestSignature $signature): bool
    {
        return $operation->method === $signature->method
            && self::normalisePath($operation->path) === self::normalisePath($signature->endpoint);
    }

    /**
     * Ramène un chemin de spec au chemin relatif que porte une `Request` : les
     * préfixes de base URL disparaissent, et le scope `/companies/{id}` du Firm
     * avec eux puisqu'il est porté par le connecteur, pas par la requête.
     */
    private static function normalisePath(string $path): string
    {
        $path = preg_replace('#^/api/external/v2#', '', $path) ?? $path;
        // Le préfixe de scope n'en est un que s'il précède une ressource :
        // `/companies/{id}` seul est l'endpoint « une entreprise du cabinet ».
        $path = preg_replace('#^/api/external/firm/v1/companies/\{[^}]+\}(?=/)#', '', $path) ?? $path;
        $path = preg_replace('#^/api/external/firm/v1#', '', $path) ?? $path;
        $path = preg_replace('/\{[^}]*\}/', '{}', $path) ?? $path;

        return rtrim($path, '/');
    }

    /**
     * @return list<SpecOperation>
     */
    private function includedOperations(): array
    {
        return array_values(array_filter(
            $this->operations,
            fn (SpecOperation $operation): bool => !array_key_exists($operation->operationId, $this->excludedOperationIds),
        ));
    }
}
