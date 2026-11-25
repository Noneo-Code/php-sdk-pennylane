<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Enums\Method;
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

    /**
     * Nombre de *nouvelles* tentatives après l'échec initial : `3` signifie
     * quatre appels HTTP au pire, `0` désactive tout rejeu.
     */
    public const int DEFAULT_MAX_RETRIES = 3;

    private const float BASE_RETRY_DELAY = 0.5;

    private const float MAX_RETRY_DELAY = 8.0;

    private const float RETRY_JITTER = 0.25;

    /**
     * Plafond appliqué à un `Retry-After` reçu du serveur : au-delà, mieux vaut
     * rendre la main à l'appelant que bloquer le process plusieurs minutes.
     */
    private const int MAX_RETRY_AFTER_DELAY = 60;

    /**
     * `PATCH` en est volontairement absent : la sémantique d'un patch partiel
     * (incrément, ajout à une liste) n'est pas idempotente en général.
     */
    private const array IDEMPOTENT_METHODS = [Method::GET, Method::HEAD, Method::PUT, Method::DELETE];

    private ?RateLimitStore $configuredRateLimitStore = null;

    private ?int $configuredMaxRetries = null;

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

    /**
     * Les connecteurs étant `final`, le nombre de tentatives se règle ici
     * plutôt qu'en redéfinissant une méthode protégée.
     */
    public function useMaxRetries(int $maxRetries): static
    {
        $this->configuredMaxRetries = max(0, $maxRetries);

        return $this;
    }

    public function getMaxRetries(): int
    {
        return $this->configuredMaxRetries ?? self::DEFAULT_MAX_RETRIES;
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
     * entreprises partageant un store — le `Psr16RateLimitStore` recommandé dès qu'un
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

            $request = $response->getRequest();
            $attempts = $this->retryAttempts()[$request] ?? 0;

            if ($attempts >= $this->getMaxRetries()) {
                unset($this->retryAttempts()[$request]);

                return $response;
            }

            $this->retryAttempts()[$request] = $attempts + 1;

            $this->wait($this->retryDelay($response, $attempts));

            $retried = $response->getConnector()->send($request);

            unset($this->retryAttempts()[$request]);

            return $retried;
        });
    }

    /**
     * Combien attendre avant la tentative n° `$attempts + 1`.
     *
     * Le serveur a le dernier mot : un `Retry-After` exploitable est honoré tel
     * quel, simplement plafonné pour qu'un en-tête aberrant ne fige pas le
     * process. Sans en-tête — le cas courant des 5xx — on retombe sur un
     * backoff exponentiel : réessayer immédiatement un serveur qui vient de
     * renvoyer 503 ne fait qu'ajouter à sa charge.
     */
    protected function retryDelay(Response $response, int $attempts): float
    {
        $header = $response->header('Retry-After');
        $retryAfter = RetryAfterHelper::parse(is_string($header) ? $header : null);

        // Un `Retry-After` nul ou déjà expiré (date passée) ne protège de rien :
        // il vaut alors une absence d'en-tête.
        if (null !== $retryAfter && $retryAfter > 0) {
            return (float) min($retryAfter, self::MAX_RETRY_AFTER_DELAY);
        }

        $delay = min(self::BASE_RETRY_DELAY * 2 ** $attempts, self::MAX_RETRY_DELAY);

        // Jitter soustractif : il désynchronise les rafales de plusieurs
        // process sans jamais franchir le plafond par le haut.
        return round($delay * (1.0 - self::RETRY_JITTER * random_int(0, 1000) / 1000), 3);
    }

    protected function wait(float $seconds): void
    {
        if ($seconds > 0) {
            usleep((int) round($seconds * 1_000_000));
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

    /**
     * Un 429 dit que la requête n'a pas été traitée : la rejouer est sûr, quelle
     * que soit la méthode. Un 5xx, lui, ne dit rien — un 502 de proxy laisse
     * entière la question de savoir si Pennylane a traité l'appel. Faute de clé
     * d'idempotence côté API, on ne rejoue alors que les méthodes qui, par
     * définition, supportent d'être répétées : rejouer un
     * `POST /customer_invoices` créerait une seconde facture.
     */
    private function isRetryableFailure(Response $response): bool
    {
        if (429 === $response->status()) {
            return true;
        }

        return $response->serverError()
            && in_array($response->getPendingRequest()->getMethod(), self::IDEMPOTENT_METHODS, true);
    }
}
