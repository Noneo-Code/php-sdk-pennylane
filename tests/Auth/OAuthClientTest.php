<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Auth;

use GuzzleHttp\RequestOptions;
use Noneo\Sdk\Pennylane\Auth\AccessToken;
use Noneo\Sdk\Pennylane\Auth\OAuthClient;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\OAuth2\GetAccessTokenRequest;
use Saloon\Http\OAuth2\GetRefreshTokenRequest;

final class OAuthClientTest extends TestCase
{
    public function testBuildsTheAuthorizationUrl(): void
    {
        $client = new OAuthClient('client-id', 'client-secret', 'https://app.example.com/callback');

        $url = $client->getAuthorizationUrl(scopes: ['ledger_entries:all'], state: 'abc123');

        self::assertSame(
            'https://app.pennylane.com/oauth/authorize?response_type=code&scope=ledger_entries%3Aall&client_id=client-id&redirect_uri=https%3A%2F%2Fapp.example.com%2Fcallback&state=abc123',
            $url,
        );
    }

    public function testExchangesTheAuthorizationCodeForAnAccessToken(): void
    {
        $client = new OAuthClient('client-id', 'client-secret', 'https://app.example.com/callback');
        $mockClient = new MockClient([
            MockResponse::make([
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
                'expires_in' => 3600,
            ]),
        ]);
        $client->connector()->withMockClient($mockClient);

        $token = $client->getAccessToken('the-code');

        self::assertInstanceOf(AccessToken::class, $token);
        self::assertSame('access-token', $token->token);
        self::assertSame('refresh-token', $token->refreshToken);
        self::assertInstanceOf(\DateTimeImmutable::class, $token->expiresAt);

        $mockClient->assertSent(function (GetAccessTokenRequest $request): bool {
            return 'oauth/oauth/token' === $request->resolveEndpoint()
                && $request->body()->all() === [
                    'grant_type' => 'authorization_code',
                    'code' => 'the-code',
                    'client_id' => 'client-id',
                    'client_secret' => 'client-secret',
                    'redirect_uri' => 'https://app.example.com/callback',
                ];
        });
    }

    public function testRefreshesAnExpiredAccessToken(): void
    {
        $client = new OAuthClient('client-id', 'client-secret', 'https://app.example.com/callback');
        $mockClient = new MockClient([
            MockResponse::make([
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
                'expires_in' => 3600,
            ]),
        ]);
        $client->connector()->withMockClient($mockClient);

        $expired = new AccessToken('old-access-token', 'refresh-token', new \DateTimeImmutable('-1 hour'));

        $refreshed = $client->refreshIfExpired($expired);

        self::assertSame('new-access-token', $refreshed->token);
        self::assertSame('new-refresh-token', $refreshed->refreshToken);
        $mockClient->assertSent(function (GetRefreshTokenRequest $request): bool {
            return 'oauth/oauth/token' === $request->resolveEndpoint()
                && 'refresh_token' === $request->body()->all()['grant_type'];
        });
    }

    public function testDoesNotRefreshAnAccessTokenThatHasNotExpired(): void
    {
        $client = new OAuthClient('client-id', 'client-secret', 'https://app.example.com/callback');
        $mockClient = new MockClient([]);
        $client->connector()->withMockClient($mockClient);

        $valid = new AccessToken('access-token', 'refresh-token', new \DateTimeImmutable('+1 hour'));

        $result = $client->refreshIfExpired($valid);

        self::assertSame($valid, $result);
        $mockClient->assertNothingSent();
    }

    public function testRefusesToRefreshWithoutARefreshToken(): void
    {
        $client = new OAuthClient('client-id', 'client-secret', 'https://app.example.com/callback');

        $this->expectException(\InvalidArgumentException::class);

        $client->refresh(new AccessToken('access-token'));
    }

    public function testAppliesTheDefaultTimeoutsToTheTokenRequest(): void
    {
        $client = new OAuthClient('client-id', 'client-secret', 'https://app.example.com/callback');
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-token', 'expires_in' => 3600]),
        ]);
        $client->connector()->withMockClient($mockClient);

        $client->getAccessToken('the-code');

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertSame(10.0, $config[RequestOptions::CONNECT_TIMEOUT] ?? null);
        self::assertSame(60.0, $config[RequestOptions::TIMEOUT] ?? null);
    }

    public function testAppliesTheTimeoutsGivenToTheConstructor(): void
    {
        $client = new OAuthClient(
            'client-id',
            'client-secret',
            'https://app.example.com/callback',
            requestTimeout: 30,
            connectTimeout: 2,
        );
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-token', 'expires_in' => 3600]),
        ]);
        $client->connector()->withMockClient($mockClient);

        $client->getAccessToken('the-code');

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertSame(2.0, $config[RequestOptions::CONNECT_TIMEOUT] ?? null);
        self::assertSame(30.0, $config[RequestOptions::TIMEOUT] ?? null);
    }
}
