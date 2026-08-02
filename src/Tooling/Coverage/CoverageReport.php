<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

final class CoverageReport
{
    /**
     * @param list<SpecOperation>   $operations
     * @param list<string>          $coveredBasenames
     * @param array<string, string> $excludedOperationIds operationId => raison de l'exclusion
     */
    public function __construct(
        private readonly array $operations,
        private readonly array $coveredBasenames,
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
            fn (SpecOperation $operation): bool => !in_array(ucfirst($operation->operationId), $this->coveredBasenames, true),
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
