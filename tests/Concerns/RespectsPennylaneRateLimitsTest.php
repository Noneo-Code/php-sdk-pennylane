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
use Saloon\Http\Request;
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
