<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Auth;

use Noneo\Sdk\Pennylane\Concerns\AppliesPennylaneTimeouts;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;

final class OAuthClient extends Connector
{
    use AppliesPennylaneTimeouts;
    use AuthorizationCodeGrant;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
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

    public function refreshIfExpired(OAuthAuthenticator $authenticator): OAuthAuthenticator
    {
        if ($authenticator->hasNotExpired()) {
            return $authenticator;
        }

        return $this->refreshAccessToken($authenticator);
    }
}
