<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

final class CoverageReport
{
    /**
     * @param list<SpecOperation> $operations
     * @param list<string>        $coveredBasenames
     */
    public function __construct(
        private readonly array $operations,
        private readonly array $coveredBasenames,
    ) {
    }

    public function total(): int
    {
        return count($this->operations);
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
            $this->operations,
            fn (SpecOperation $operation): bool => !in_array(ucfirst($operation->operationId), $this->coveredBasenames, true),
        ));
    }
}
