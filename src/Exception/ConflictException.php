<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

final class ConflictException extends PennylaneException
{
    /**
     * Best-effort extraction: Pennylane's documented conflict message doesn't
     * embed the existing resource's id (only free text), so this returns null
     * unless a future response embeds one rather than guessing a field.
     */
    public function existingId(): ?int
    {
        if (1 === preg_match('/\bid\s*:?\s*(\d+)\b/i', $this->error(), $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
