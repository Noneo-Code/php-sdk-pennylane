<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

final class ValidationException extends PennylaneException
{
    /**
     * No structured error code is documented for 422 responses (only free
     * text), so this reads it from details() — always null today — rather
     * than guessing a field name that isn't in the spec.
     */
    public function errorCode(): ?string
    {
        $code = $this->details()['error_code'] ?? null;

        return is_string($code) ? $code : null;
    }
}
