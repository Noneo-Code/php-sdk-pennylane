<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Helpers\RetryAfterHelper;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;

/**
 * Pennylane allows 25 requests every 5 seconds, per token — shared between
 * every process using that token. `HasRateLimits` throttles our own outgoing
 * pace locally; retrying an actual 429/5xx response is handled separately
 * below so the wait can use the server's `retry-after` instead of a blind
 * backoff, and so it stays fakeable in tests (no dependency on Saloon's own
 * `usleep`-based delay).
 */
trait RespectsPennylaneRateLimits
{
    use HasRateLimits;

    private ?RateLimitStore $configuredRateLimitStore = null;

    /**
     * @var array<int, int>
     */
    private array $retryAttemptsByRequest = [];

    public function useRateLimitStore(RateLimitStore $store): static
    {
        $this->configuredRateLimitStore = $store;

        return $this;
    }

    public function getRateLimitStore(): RateLimitStore
    {
        return $this->resolveRateLimitStore();
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return $this->configuredRateLimitStore ??= new MemoryStore();
    }

    /**
     * @return array<Limit>
     */
    protected function resolveLimits(): array
    {
        return [
            Limit::allow(25)->everySeconds(5)->sleep(),
        ];
    }

    public function bootRespectsPennylaneRateLimits(PendingRequest $pendingRequest): void
    {
        $this->detectTooManyAttempts = false;

        $pendingRequest->middleware()->onResponse(function (Response $response): Response {
            if (!$this->isRetryableFailure($response)) {
                return $response;
            }

            $header = $response->header('Retry-After');
            $retryAfter = RetryAfterHelper::parse(is_string($header) ? $header : null);

            if (null === $retryAfter) {
                return $response;
            }

            $request = $response->getRequest();
            $requestId = spl_object_id($request);
            $attempts = $this->retryAttemptsByRequest[$requestId] ?? 0;

            if ($attempts >= $this->maxRetryAttempts()) {
                unset($this->retryAttemptsByRequest[$requestId]);

                return $response;
            }

            $this->retryAttemptsByRequest[$requestId] = $attempts + 1;

            $this->wait($retryAfter);

            return $response->getConnector()->send($request);
        });
    }

    protected function maxRetryAttempts(): int
    {
        return 1;
    }

    protected function wait(int $seconds): void
    {
        if ($seconds > 0) {
            sleep($seconds);
        }
    }

    private function isRetryableFailure(Response $response): bool
    {
        return 429 === $response->status() || $response->serverError();
    }
}
