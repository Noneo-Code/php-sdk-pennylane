<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
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
     * Nombre de tentatives déjà consommées, par requête. Une `WeakMap` plutôt
     * qu'un tableau indexé sur `spl_object_id` : PHP recycle ces identifiants
     * dès qu'un objet est libéré, si bien qu'une nouvelle requête pouvait
     * hériter du compteur d'une requête morte et perdre son unique nouvelle
     * tentative. La `WeakMap` se vide d'elle-même quand la requête disparaît.
     *
     * @var \WeakMap<Request, int>|null
     */
    private ?\WeakMap $retryAttempts = null;

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

    /**
     * Le quota Pennylane est décompté par jeton, et par jeton seulement : la
     * clé ne porte donc pas le nom du connecteur. Un `PennylaneFirm` et ses
     * `CompanyScope` tirent sur le même quota — les compter séparément
     * autoriserait un cabinet suivant trois entreprises à quatre fois le débit
     * réellement accordé. Symétriquement, sans le jeton dans la clé, deux
     * entreprises partageant un store — le `PsrStore` recommandé dès qu'un
     * process est répliqué — se disputeraient un unique compteur.
     *
     * Le jeton est haché : il n'a rien à faire en clair dans une clé de cache.
     */
    protected function getLimiterPrefix(): ?string
    {
        return 'pennylane:'.$this->rateLimitIdentity();
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
            $attempts = $this->retryAttempts()[$request] ?? 0;

            if ($attempts >= $this->maxRetryAttempts()) {
                unset($this->retryAttempts()[$request]);

                return $response;
            }

            $this->retryAttempts()[$request] = $attempts + 1;

            $this->wait($retryAfter);

            $retried = $response->getConnector()->send($request);

            unset($this->retryAttempts()[$request]);

            return $retried;
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

    protected function countTrackedRetries(): int
    {
        return count($this->retryAttempts());
    }

    /**
     * @return \WeakMap<Request, int>
     */
    private function retryAttempts(): \WeakMap
    {
        return $this->retryAttempts ??= new \WeakMap();
    }

    private function rateLimitIdentity(): string
    {
        $authenticator = $this->getAuthenticator();

        $token = match (true) {
            $authenticator instanceof OAuthAuthenticator => $authenticator->getAccessToken(),
            $authenticator instanceof TokenAuthenticator => $authenticator->token,
            default => null,
        };

        // Un authenticator maison ne laisse aucun jeton à isoler : on retombe
        // sur un compteur partagé, le comportement d'avant.
        return null === $token ? 'shared' : substr(hash('xxh128', $token), 0, 12);
    }

    private function isRetryableFailure(Response $response): bool
    {
        return 429 === $response->status() || $response->serverError();
    }
}
