<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Un enum backed du SDK annoté, et les valeurs de ses cas.
 */
final class EnumTarget
{
    /**
     * @param list<string> $values
     */
    public function __construct(
        public readonly string $subject,
        public readonly DocumentedBy $documentedBy,
        public readonly array $values,
    ) {
    }
}
