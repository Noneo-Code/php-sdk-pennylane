<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class PennylaneTest extends TestCase
{
    public function testResolvesTheCompanyBaseUrl(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));

        self::assertSame('https://app.pennylane.com/api/external/v2', $pennylane->resolveBaseUrl());
    }

    public function testSetsTheBearerAuthorizationHeaderFromTheGivenAuthenticator(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('company-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $mockClient->assertSent(function (Request $request, Response $response): bool {
            return 'Bearer company-token' === $response->getPendingRequest()->headers()->get('Authorization');
        });
    }

    public function testSetsTheBearerAuthorizationHeaderFromAnOAuthAccessTokenAuthenticator(): void
    {
        $pennylane = new Pennylane(new AccessTokenAuthenticator('oauth-access-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $mockClient->assertSent(function (Request $request, Response $response): bool {
            return 'Bearer oauth-access-token' === $response->getPendingRequest()->headers()->get('Authorization');
        });
    }
}
