<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

final class CurlHttpFetcher implements HttpFetcher
{
    /**
     * @param non-empty-string $userAgent
     */
    public function __construct(
        private readonly int $delayMicroseconds = 300_000,
        private readonly string $userAgent = 'noneo/sdk-pennylane fetch-openapi',
        private readonly int $timeoutSeconds = 30,
    ) {
    }

    public function get(string $url): string
    {
        if ($this->delayMicroseconds > 0) {
            usleep($this->delayMicroseconds);
        }

        $handle = curl_init($url);

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $body = curl_exec($handle);
        $error = curl_error($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        if (!is_string($body)) {
            throw new \RuntimeException("Échec de récupération de {$url} : {$error}");
        }

        if (0 !== $statusCode && $statusCode >= 400) {
            throw new \RuntimeException("Échec de récupération de {$url} : HTTP {$statusCode}");
        }

        return $body;
    }
}
