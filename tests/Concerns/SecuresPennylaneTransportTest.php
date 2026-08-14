<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Concerns;

use GuzzleHttp\RequestOptions;
use Noneo\Sdk\Pennylane\Auth\OAuthClient;
use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\SecuresPennylaneTransport;
use Noneo\Sdk\Pennylane\Exception\InsecureBaseUrlException;
use Noneo\Sdk\Pennylane\Exception\RedirectException;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class SecuresPennylaneTransportTest extends TestCase
{
    /**
     * Suivre un 301 rejouerait l'en-tête `Authorization` vers l'hôte annoncé
     * par la redirection — un tiers, potentiellement. Une API JSON ne redirige
     * jamais légitimement : on ne suit pas.
     */
    public function testDoesNotFollowRedirects(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send($this->probeRequest());

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertFalse($config[RequestOptions::ALLOW_REDIRECTS] ?? null);
    }

    public function testTurnsARedirectIntoAnExplicitException(): void
    {
        $pennylane = new Pennylane('a-token', maxRetries: 0);
        $mockClient = new MockClient([
            MockResponse::make('', 301, ['Location' => 'https://evil.example.com/api']),
            MockResponse::make(['id' => 1]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        try {
            $pennylane->connector()->send($this->probeRequest());
            self::fail('Expected a RedirectException to be thrown.');
        } catch (RedirectException $exception) {
            self::assertSame(301, $exception->status());
            self::assertSame('https://evil.example.com/api', $exception->location());
        }

        self::assertCount(1, $mockClient->getRecordedResponses(), 'Aucune seconde requête ne doit partir.');
    }

    #[DataProvider('provideConnectors')]
    public function testSendsAUserAgentIdentifyingTheSdkAndItsVersion(Connector $connector): void
    {
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $connector->withMockClient($mockClient);

        $connector->send($this->probeRequest());

        $userAgent = $mockClient->getLastResponse()?->getPendingRequest()->headers()->get('User-Agent');

        self::assertSame('noneo-sdk-pennylane/'.Pennylane::VERSION, $userAgent);
    }

    /**
     * @return iterable<string, array{Connector}>
     */
    public static function provideConnectors(): iterable
    {
        yield 'company' => [(new Pennylane('a-token'))->connector()];
        yield 'firm' => [(new PennylaneFirm('a-token'))->connector()];
        yield 'company scope' => [(new PennylaneFirm('a-token'))->company(42)->connector()];
        yield 'oauth' => [new OAuthClient('id', 'secret', 'https://noneo.fr/callback')];
    }

    public function testTheVersionMatchesTheLatestPublishedChangelogEntry(): void
    {
        $changelog = file_get_contents(__DIR__.'/../../CHANGELOG.md');

        self::assertIsString($changelog);

        $matches = [];
        preg_match('/^## \[(\d+\.\d+\.\d+)\]/m', $changelog, $matches);

        self::assertSame(
            Pennylane::VERSION,
            $matches[1] ?? null,
            'Pennylane::VERSION doit suivre la dernière version publiée du CHANGELOG.',
        );
    }

    /**
     * Les trois `resolveBaseUrl()` sont en dur et en HTTPS : ce garde-fou ne
     * sert à rien aujourd'hui, et à tout le jour où une surcharge de base URL
     * sera introduite pour une sandbox ou des tests d'intégration.
     */
    public function testRefusesToSendOverPlainHttp(): void
    {
        $connector = $this->connectorWithBaseUrl('http://app.pennylane.com/api/external/v2');
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $this->expectException(InsecureBaseUrlException::class);
        $this->expectExceptionMessage('http://app.pennylane.com/api/external/v2/ledger_entries');

        $connector->send($this->probeRequest());
    }

    #[DataProvider('provideLoopbackBaseUrls')]
    public function testAllowsPlainHttpOnLoopbackHostsForIntegrationTesting(string $baseUrl): void
    {
        $connector = $this->connectorWithBaseUrl($baseUrl);
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $connector->withMockClient($mockClient);

        $connector->send($this->probeRequest());

        self::assertSame(200, $mockClient->getLastResponse()?->status());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideLoopbackBaseUrls(): iterable
    {
        yield 'localhost' => ['http://localhost:8080/api'];
        yield 'ipv4 loopback' => ['http://127.0.0.1:8080/api'];
        yield 'ipv6 loopback' => ['http://[::1]:8080/api'];
    }

    public function testAcceptsHttps(): void
    {
        $connector = $this->connectorWithBaseUrl('https://app.pennylane.com/api/external/v2');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $connector->withMockClient($mockClient);

        $connector->send($this->probeRequest());

        self::assertSame(200, $mockClient->getLastResponse()?->status());
    }

    private function connectorWithBaseUrl(string $baseUrl): Connector
    {
        return new class($baseUrl) extends Connector {
            use HandlesPennylaneErrors;
            use SecuresPennylaneTransport;

            public function __construct(private readonly string $baseUrl)
            {
            }

            public function resolveBaseUrl(): string
            {
                return $this->baseUrl;
            }
        };
    }

    private function probeRequest(): Request
    {
        return new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        };
    }
}
