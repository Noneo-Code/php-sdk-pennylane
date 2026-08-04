<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Webhook;

final readonly class SignatureVerifier
{
    public function __construct(
        #[\SensitiveParameter] private string $secret,
        private int $toleranceSeconds = 300,
    ) {
    }

    public function verify(string $rawBody, string $signatureHeader): bool
    {
        $parsed = self::parseHeader($signatureHeader);

        if (null === $parsed) {
            return false;
        }

        [$timestamp, $signature] = $parsed;

        if (abs(time() - $timestamp) > $this->toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $this->secret);

        return hash_equals($expected, $signature);
    }

    /** @return array{0: int, 1: string}|null */
    private static function parseHeader(string $signatureHeader): ?array
    {
        $parts = [];

        foreach (explode(',', $signatureHeader) as $pair) {
            $segments = explode('=', $pair, 2);

            if (2 !== count($segments)) {
                return null;
            }

            [$key, $value] = $segments;
            $parts[$key] = $value;
        }

        if (!isset($parts['t'], $parts['v1']) || 1 !== preg_match('/^\d+$/', $parts['t'])) {
            return null;
        }

        return [(int) $parts['t'], $parts['v1']];
    }
}
