<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Auth;

/**
 * Jeton d'accès OAuth, exprimé dans le vocabulaire du SDK : aucun type Saloon
 * ne transite chez le consommateur, qui persiste ce DTO et le repasse à
 * {@see OAuthClient::refreshIfExpired()}.
 */
final readonly class AccessToken
{
    /**
     * `$token` et `$refreshToken` sont marqués sensibles : le second survit de
     * loin au premier et n'a rien à faire dans une trace.
     */
    public function __construct(
        #[\SensitiveParameter] public string $token,
        #[\SensitiveParameter] public ?string $refreshToken = null,
        public ?\DateTimeImmutable $expiresAt = null,
    ) {
    }

    /**
     * Sans échéance connue, le jeton est réputé valide : c'est au serveur de
     * trancher, et `refreshIfExpired()` ne rafraîchit alors jamais à l'aveugle.
     */
    public function hasExpired(?\DateTimeImmutable $now = null): bool
    {
        if (!$this->expiresAt instanceof \DateTimeImmutable) {
            return false;
        }

        return $this->expiresAt <= ($now ?? new \DateTimeImmutable());
    }
}
