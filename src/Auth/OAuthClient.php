<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Auth;

use Noneo\Sdk\Pennylane\Http\Connector\OAuthConnector;
use Saloon\Contracts\OAuthAuthenticator;

/**
 * Flux OAuth Authorization Code de Pennylane, exprimé sans type Saloon : les
 * jetons entrent et sortent en {@see AccessToken}. Le jeton d'accès obtenu
 * s'utilise ensuite comme n'importe quel Bearer :
 * `new Pennylane($accessToken->token)`.
 */
final class OAuthClient
{
    private readonly OAuthConnector $connector;

    /**
     * `$clientSecret` est le seul secret ici : `$clientId` et `$redirectUri`
     * sont publics par construction — ils transitent dans l'URL d'autorisation,
     * visible de l'utilisateur.
     */
    public function __construct(
        string $clientId,
        #[\SensitiveParameter] string $clientSecret,
        string $redirectUri,
        ?float $requestTimeout = null,
        ?float $connectTimeout = null,
    ) {
        $this->connector = new OAuthConnector(
            $clientId,
            $clientSecret,
            $redirectUri,
            $requestTimeout,
            $connectTimeout,
        );
    }

    /**
     * Accès au transport Saloon sous-jacent, hors contrat de compatibilité.
     *
     * @internal réservé aux tests du SDK
     */
    public function connector(): OAuthConnector
    {
        return $this->connector;
    }

    /**
     * @param list<string> $scopes
     */
    public function getAuthorizationUrl(array $scopes = [], ?string $state = null): string
    {
        return $this->connector->getAuthorizationUrl($scopes, $state);
    }

    /**
     * L'état généré par {@see getAuthorizationUrl()} quand aucun n'est fourni,
     * à persister côté consommateur pour la vérification anti-CSRF au retour.
     */
    public function getState(): ?string
    {
        return $this->connector->getState();
    }

    public function getAccessToken(string $code): AccessToken
    {
        return $this->toAccessToken($this->connector->getAccessToken($code));
    }

    public function refresh(#[\SensitiveParameter] AccessToken $token): AccessToken
    {
        if (null === $token->refreshToken) {
            throw new \InvalidArgumentException('Ce jeton ne porte pas de refresh token : impossible de le rafraîchir.');
        }

        return $this->toAccessToken($this->connector->refreshAccessToken($token->refreshToken));
    }

    /**
     * `$token` porte le jeton de rafraîchissement, dont la durée de vie dépasse
     * de loin celle du jeton d'accès : il n'a rien à faire dans une trace.
     */
    public function refreshIfExpired(#[\SensitiveParameter] AccessToken $token): AccessToken
    {
        return $token->hasExpired() ? $this->refresh($token) : $token;
    }

    private function toAccessToken(#[\SensitiveParameter] OAuthAuthenticator $authenticator): AccessToken
    {
        return new AccessToken(
            token: $authenticator->getAccessToken(),
            refreshToken: $authenticator->getRefreshToken(),
            expiresAt: $authenticator->getExpiresAt(),
        );
    }
}
