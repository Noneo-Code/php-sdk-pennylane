<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperation;

/**
 * Compare deux jeux d'opérations sur ce qui engage le SDK — l'`operationId`, la
 * méthode et le chemin. Un diff texte des fichiers de spec, lui, signalerait
 * chaque réordonnancement de `paths` ou reformulation de description comme une
 * dérive : la veille hebdomadaire ne serait plus lue au bout d'un mois.
 */
final class OperationDiff
{
    /**
     * @param list<SpecOperation>                                      $added
     * @param list<SpecOperation>                                      $removed
     * @param list<array{before: SpecOperation, after: SpecOperation}> $changed
     */
    private function __construct(
        public readonly array $added,
        public readonly array $removed,
        public readonly array $changed,
    ) {
    }

    /**
     * @param list<SpecOperation> $committed
     * @param list<SpecOperation> $fresh
     */
    public static function between(array $committed, array $fresh): self
    {
        $before = self::byOperationId($committed);
        $after = self::byOperationId($fresh);

        $added = array_values(array_diff_key($after, $before));
        $removed = array_values(array_diff_key($before, $after));

        $changed = [];
        foreach (array_intersect_key($before, $after) as $operationId => $operation) {
            if (self::signature($operation) !== self::signature($after[$operationId])) {
                $changed[] = ['before' => $operation, 'after' => $after[$operationId]];
            }
        }

        return new self($added, $removed, $changed);
    }

    public function hasDrift(): bool
    {
        return [] !== $this->added || [] !== $this->removed || [] !== $this->changed;
    }

    public function toMarkdown(): string
    {
        if (!$this->hasDrift()) {
            return 'Aucune dérive : les opérations committées correspondent à la spec publiée.';
        }

        $sections = [];

        if ([] !== $this->added) {
            $sections[] = self::section('Ajoutées', array_map(
                static fn (SpecOperation $operation): string => '- `'.self::signature($operation).'` (`'.$operation->operationId.'`)',
                $this->added,
            ));
        }

        if ([] !== $this->removed) {
            $sections[] = self::section('Supprimées', array_map(
                static fn (SpecOperation $operation): string => '- `'.self::signature($operation).'` (`'.$operation->operationId.'`)',
                $this->removed,
            ));
        }

        if ([] !== $this->changed) {
            $sections[] = self::section('Modifiées', array_map(
                static fn (array $change): string => sprintf(
                    '- `%s` : `%s` → `%s`',
                    $change['before']->operationId,
                    self::signature($change['before']),
                    self::signature($change['after']),
                ),
                $this->changed,
            ));
        }

        return implode("\n\n", $sections);
    }

    /**
     * @param list<string> $lines
     */
    private static function section(string $title, array $lines): string
    {
        return sprintf("### %s (%d)\n\n%s", $title, count($lines), implode("\n", $lines));
    }

    private static function signature(SpecOperation $operation): string
    {
        return $operation->method.' '.$operation->path;
    }

    /**
     * @param list<SpecOperation> $operations
     *
     * @return array<string, SpecOperation>
     */
    private static function byOperationId(array $operations): array
    {
        $indexed = [];

        foreach ($operations as $operation) {
            $indexed[$operation->operationId] = $operation;
        }

        return $indexed;
    }
}
