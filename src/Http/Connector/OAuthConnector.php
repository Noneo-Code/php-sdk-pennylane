<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http\Connector;

use Noneo\Sdk\Pennylane\Concerns\AppliesPennylaneTimeouts;
use Noneo\Sdk\Pennylane\Concerns\SecuresPennylaneTransport;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;

/**
 * Transport Saloon du flux Authorization Code. Détail d'implémentation de
 * {@see \Noneo\Sdk\Pennylane\Auth\OAuthClient}.
 *
 * @internal
 */
final class OAuthConnector extends Connector
{
    use AppliesPennylaneTimeouts;
    use AuthorizationCodeGrant;
    use SecuresPennylaneTransport;

    /**
     * `$clientSecret` est le seul secret ici : `$clientId` et `$redirectUri`
     * sont publics par construction — ils transitent dans l'URL d'autorisation,
     * visible de l'utilisateur.
     */
    public function __construct(
        private readonly string $clientId,
        #[\SensitiveParameter] private readonly string $clientSecret,
        private readonly string $redirectUri,
        ?float $requestTimeout = null,
        ?float $connectTimeout = null,
    ) {
        $this->useTimeouts($requestTimeout, $connectTimeout);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com';
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId($this->clientId)
            ->setClientSecret($this->clientSecret)
            ->setRedirectUri($this->redirectUri)
            ->setAuthorizeEndpoint('oauth/authorize')
            ->setTokenEndpoint('oauth/oauth/token');
    }
}
