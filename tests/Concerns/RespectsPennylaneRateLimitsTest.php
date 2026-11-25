<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Concerns;

use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Exception\RateLimitException;
use Noneo\Sdk\Pennylane\Exception\ServerException;
use Noneo\Sdk\Pennylane\Http\Connector\FirmConnector;
use Noneo\Sdk\Pennylane\Http\Connector\PennylaneConnector;
use Noneo\Sdk\Pennylane\Http\Connector\Psr16RateLimitStore;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

final class RespectsPennylaneRateLimitsTest extends TestCase
{
    protected function setUp(): void
    {
        MemoryStore::clear();
    }

    public function testRetriesAfterA429WithRetryAfterHeader(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests', 'status' => 429], 429, ['Retry-After' => '3']),
            MockResponse::make(['id' => 1], 200),
        ]));

        $response = $connector->send($this->makeRequest());

        self::assertSame(200, $response->status());
        self::assertSame([3.0], $connector->waits);
    }

    public function testStopsAfterThreeRetriesByDefaultAndThrows(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient(array_fill(0, 4, MockResponse::make(
            ['error' => 'Too Many Requests', 'status' => 429],
            429,
            ['Retry-After' => '3'],
        ))));

        $this->expectException(RateLimitException::class);

        try {
            $connector->send($this->makeRequest());
        } finally {
            self::assertSame([3.0, 3.0, 3.0], $connector->waits);
        }
    }

    public function testDisablesEveryRetryWhenMaxRetriesIsZero(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->useMaxRetries(0);
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests', 'status' => 429], 429, ['Retry-After' => '3']),
        ]));

        $this->expectException(RateLimitException::class);

        try {
            $connector->send($this->makeRequest());
        } finally {
            self::assertSame([], $connector->waits);
        }
    }

    public function testStopsAfterTheConfiguredNumberOfRetries(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->useMaxRetries(1);
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests', 'status' => 429], 429, ['Retry-After' => '3']),
            MockResponse::make(['error' => 'Too Many Requests', 'status' => 429], 429, ['Retry-After' => '3']),
        ]));

        $this->expectException(RateLimitException::class);

        try {
            $connector->send($this->makeRequest());
        } finally {
            self::assertSame([3.0], $connector->waits);
        }
    }

    public function testHonoursTheMaxRetriesGivenToTheConnectorConstructor(): void
    {
        self::assertSame(1, (new Pennylane('a-token', maxRetries: 1))->connector()->getMaxRetries());
        self::assertSame(0, (new PennylaneFirm('a-token', maxRetries: 0))->connector()->getMaxRetries());
        self::assertSame(
            Pennylane::DEFAULT_MAX_RETRIES,
            (new Pennylane('a-token'))->connector()->getMaxRetries(),
        );
    }

    public function testPropagatesTheFirmMaxRetriesToItsCompanyScope(): void
    {
        $firm = new PennylaneFirm('a-token', maxRetries: 0);

        self::assertSame(0, $firm->company(42)->connector()->getMaxRetries());
    }

    public function testCapsAnAbsurdRetryAfterHeader(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests'], 429, ['Retry-After' => '3600']),
            MockResponse::make(['id' => 1], 200),
        ]));

        $connector->send($this->makeRequest());

        self::assertSame([60.0], $connector->waits);
    }

    public function testCapsTheExponentialBackoffAtEightSeconds(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->useMaxRetries(6);
        $connector->withMockClient(new MockClient([
            ...array_fill(0, 6, MockResponse::make(['error' => 'Service Unavailable'], 503)),
            MockResponse::make(['id' => 1], 200),
        ]));

        $connector->send($this->makeRequest());

        self::assertCount(6, $connector->waits);

        foreach ($connector->waits as $wait) {
            self::assertLessThanOrEqual(8.0, $wait);
        }

        // Les deux dernières tentatives sont au plafond : 0,5 × 2⁵ = 16 > 8.
        self::assertGreaterThanOrEqual(6.0, $connector->waits[4]);
        self::assertGreaterThanOrEqual(6.0, $connector->waits[5]);
    }

    public function testSolicitsTheInjectedPsrStore(): void
    {
        $cache = new RecordingCache();

        $connector = new RateLimitedTestConnector();
        $connector->useRateLimitStore(new Psr16RateLimitStore($cache));
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1], 200)]));

        $connector->send($this->makeRequest());

        self::assertNotEmpty($cache->getCalls);
        self::assertNotEmpty($cache->setCalls);
    }

    public function testTheCacheGivenToTheConstructorOnlyReceivesPsr16Keys(): void
    {
        $cache = new RecordingCache();

        $connector = new PennylaneConnector('a-token', $cache);
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1], 200)]));

        $connector->send($this->makeRequest());

        self::assertNotEmpty($cache->setCalls);
        foreach ([...$cache->getCalls, ...$cache->setCalls] as $key) {
            self::assertMatchesRegularExpression('/^[A-Za-z0-9_.]{1,64}$/', $key);
        }
    }

    public function testTheFirmConnectorAlsoOnlyGivesPsr16Keys(): void
    {
        $cache = new RecordingCache();

        $connector = new FirmConnector('a-token', $cache);
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1], 200)]));

        $connector->send($this->makeRequest());

        self::assertNotEmpty($cache->setCalls);
    }

    public function testSegmentsTheLimiterKeyByToken(): void
    {
        $first = new RateLimitedTestConnector();
        $first->authenticate(new TokenAuthenticator('token-societe-a'));

        $second = new RateLimitedTestConnector();
        $second->authenticate(new TokenAuthenticator('token-societe-b'));

        self::assertNotSame($this->limiterNames($first), $this->limiterNames($second));
    }

    public function testReusesTheSameLimiterKeyForTheSameToken(): void
    {
        $first = new RateLimitedTestConnector();
        $first->authenticate(new TokenAuthenticator('token-partage'));

        $second = new RateLimitedTestConnector();
        $second->authenticate(new TokenAuthenticator('token-partage'));

        self::assertSame($this->limiterNames($first), $this->limiterNames($second));
    }

    public function testNeverExposesTheRawTokenInTheLimiterKey(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->authenticate(new TokenAuthenticator('un-secret-tres-reconnaissable'));

        foreach ($this->limiterNames($connector) as $name) {
            self::assertStringNotContainsString('un-secret-tres-reconnaissable', $name);
        }
    }

    public function testForgetsTheRetryCounterOnceTheRequestSucceeds(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->authenticate(new TokenAuthenticator('a-token'));
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests'], 429, ['Retry-After' => '1']),
            MockResponse::make(['id' => 1], 200),
        ]));

        $connector->send($this->makeRequest());

        self::assertSame(0, $connector->trackedRetryCount());
    }

    public function testRetriesEachRequestOnItsOwnBudget(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->authenticate(new TokenAuthenticator('a-token'));
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests'], 429, ['Retry-After' => '1']),
            MockResponse::make(['id' => 1], 200),
            MockResponse::make(['error' => 'Too Many Requests'], 429, ['Retry-After' => '1']),
            MockResponse::make(['id' => 2], 200),
        ]));

        $connector->send($this->makeRequest());
        $second = $connector->send($this->makeRequest());

        self::assertSame(200, $second->status());
        self::assertSame([1.0, 1.0], $connector->waits);
    }

    public function testRetriesA503WithoutRetryAfterUsingAnExponentialBackoff(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Service Unavailable'], 503),
            MockResponse::make(['error' => 'Service Unavailable'], 503),
            MockResponse::make(['error' => 'Service Unavailable'], 503),
            MockResponse::make(['id' => 1], 200),
        ]));

        $response = $connector->send($this->makeRequest());

        self::assertSame(200, $response->status());
        self::assertCount(3, $connector->waits);

        // Base 0,5 s doublée à chaque tentative, avec un jitter soustractif de
        // 25 % au plus : les fenêtres restent disjointes et croissantes.
        foreach ([[0.375, 0.5], [0.75, 1.0], [1.5, 2.0]] as $attempt => [$min, $max]) {
            self::assertGreaterThanOrEqual($min, $connector->waits[$attempt]);
            self::assertLessThanOrEqual($max, $connector->waits[$attempt]);
        }
    }

    public function testNeverReplaysAPostOnAServerError(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Bad Gateway'], 502),
            MockResponse::make(['id' => 1], 200),
        ]));

        $this->expectException(ServerException::class);

        try {
            $connector->send($this->makeRequest(Method::POST));
        } finally {
            // Pennylane n'offre aucune clé d'idempotence : un 502 ne dit pas si
            // la facture a été créée. Rejouer, c'est risquer le doublon.
            self::assertSame([], $connector->waits);
        }
    }

    public function testNeverReplaysAPatchOnAServerError(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Service Unavailable'], 503),
            MockResponse::make(['id' => 1], 200),
        ]));

        $this->expectException(ServerException::class);

        try {
            $connector->send($this->makeRequest(Method::PATCH));
        } finally {
            self::assertSame([], $connector->waits);
        }
    }

    public function testReplaysAPostOnA429(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests'], 429, ['Retry-After' => '2']),
            MockResponse::make(['id' => 1], 200),
        ]));

        // Un 429 dit que la requête n'a pas été traitée : la rejouer est sûr,
        // quelle que soit la méthode.
        $response = $connector->send($this->makeRequest(Method::POST));

        self::assertSame(200, $response->status());
        self::assertSame([2.0], $connector->waits);
    }

    #[DataProvider('idempotentMethods')]
    public function testReplaysIdempotentMethodsOnAServerError(Method $method): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Bad Gateway'], 502),
            MockResponse::make(['id' => 1], 200),
        ]));

        $response = $connector->send($this->makeRequest($method));

        self::assertSame(200, $response->status());
        self::assertCount(1, $connector->waits);
    }

    /**
     * @return iterable<string, array{Method}>
     */
    public static function idempotentMethods(): iterable
    {
        yield 'GET' => [Method::GET];
        yield 'PUT' => [Method::PUT];
        yield 'DELETE' => [Method::DELETE];
        yield 'HEAD' => [Method::HEAD];
    }

    /**
     * @return list<string>
     */
    private function limiterNames(RateLimitedTestConnector $connector): array
    {
        return array_values(array_map(
            static fn (Limit $limit): string => $limit->getName(),
            $connector->getLimits(),
        ));
    }

    private function makeRequest(Method $method = Method::GET): Request
    {
        return new class($method) extends Request {
            public function __construct(protected Method $method)
            {
            }

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        };
    }
}

final class RateLimitedTestConnector extends Connector
{
    use HandlesPennylaneErrors;
    use RespectsPennylaneRateLimits;

    /** @var list<float> */
    public array $waits = [];

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/v2';
    }

    public function trackedRetryCount(): int
    {
        return $this->countTrackedRetries();
    }

    protected function wait(float $seconds): void
    {
        $this->waits[] = $seconds;
    }
}

/**
 * Cache PSR-16 aussi strict sur les clés qu'un cache réel (Symfony refuse
 * `{}()/\\@:`) : un faux cache permissif a laissé passer en 1.0.0 des clés
 * `pennylane:…` refusées en production.
 */
final class RecordingCache implements CacheInterface
{
    /** @var list<string> */
    public array $getCalls = [];

    /** @var list<string> */
    public array $setCalls = [];

    public function get(string $key, mixed $default = null): mixed
    {
        self::valider($key);
        $this->getCalls[] = $key;

        return $default;
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        self::valider($key);
        $this->setCalls[] = $key;

        return true;
    }

    private static function valider(string $key): void
    {
        if ('' === $key || false !== strpbrk($key, '{}()/\\@:')) {
            throw new class(sprintf('Clé PSR-16 invalide : « %s ».', $key)) extends \InvalidArgumentException implements InvalidArgumentException {};
        }
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }

    /**
     * @param iterable<string> $keys
     *
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return [];
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        return true;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return true;
    }

    public function has(string $key): bool
    {
        return false;
    }
}
