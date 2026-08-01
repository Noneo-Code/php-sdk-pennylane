<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

final class ForbiddenException extends PennylaneException
{
    /**
     * Best-effort extraction: Pennylane embeds the missing scope as free text
     * in the error message (`Access to this resource requires scope "ledger".`)
     * rather than as a structured field, so this returns null when the pattern
     * isn't found instead of guessing.
     */
    public function missingScope(): ?string
    {
        if (1 === preg_match('/scope "([^"]+)"/', $this->error(), $matches)) {
            return $matches[1];
        }

        return null;
    }
}
