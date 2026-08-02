<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Concerns;

use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Exception\RateLimitException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Request;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Stores\PsrStore;

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
        self::assertSame([3], $connector->waits);
    }

    public function testStopsRetryingAndThrowsAfterOneRetryStillFails(): void
    {
        $connector = new RateLimitedTestConnector();
        $connector->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Too Many Requests', 'status' => 429], 429, ['Retry-After' => '3']),
            MockResponse::make(['error' => 'Too Many Requests', 'status' => 429], 429, ['Retry-After' => '3']),
        ]));

        $this->expectException(RateLimitException::class);

        try {
            $connector->send($this->makeRequest());
        } finally {
            self::assertSame([3], $connector->waits);
        }
    }

    public function testSolicitsTheInjectedPsrStore(): void
    {
        $cache = new RecordingCache();

        $connector = new RateLimitedTestConnector();
        $connector->useRateLimitStore(new PsrStore($cache));
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1], 200)]));

        $connector->send($this->makeRequest());

        self::assertNotEmpty($cache->getCalls);
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
        self::assertSame([1, 1], $connector->waits);
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

    private function makeRequest(): Request
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

final class RateLimitedTestConnector extends Connector
{
    use HandlesPennylaneErrors;
    use RespectsPennylaneRateLimits;

    /** @var array<int, int> */
    public array $waits = [];

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/v2';
    }

    public function trackedRetryCount(): int
    {
        return $this->countTrackedRetries();
    }

    protected function wait(int $seconds): void
    {
        $this->waits[] = $seconds;
    }
}

final class RecordingCache implements CacheInterface
{
    /** @var list<string> */
    public array $getCalls = [];

    /** @var list<string> */
    public array $setCalls = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $this->getCalls[] = $key;

        return $default;
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $this->setCalls[] = $key;

        return true;
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
