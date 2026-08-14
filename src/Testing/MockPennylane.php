<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Testing;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\Assert;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

/**
 * Double de test du SDK pour les consommateurs : réponses simulées associées
 * par méthode HTTP et chemin (wildcards `*` au sens de `fnmatch()`), ou
 * servies en file séquentielle quand la liste n'a pas de clés. Aucun symbole
 * Saloon n'entre ni ne sort.
 *
 * Les chemins sont relatifs à la base de l'API : `/journals/42` côté Company,
 * `/companies/12/journals` côté Firm.
 */
final class MockPennylane
{
    /**
     * Préfixes retirés des URLs avant matching et enregistrement, si bien que
     * les chemins s'écrivent comme dans la documentation Pennylane — scope
     * d'entreprise inclus : `/companies/12/journals`.
     */
    private const array BASE_PATHS = ['/api/external/firm/v1', '/api/external/v2'];

    /**
     * @var list<array{method: string, pattern: string, response: MockedResponse}>
     */
    private array $expectations = [];

    /**
     * @var list<MockedResponse>
     */
    private array $queue = [];

    private readonly bool $sequential;

    private readonly MockClient $mockClient;

    /**
     * @var list<array{method: string, path: string, asserted: bool}>
     */
    private array $recorded = [];

    /**
     * @param array<int|string, MockedResponse> $responses
     */
    private function __construct(array $responses)
    {
        $this->sequential = array_is_list($responses);

        foreach ($responses as $key => $response) {
            if (is_int($key) !== $this->sequential) {
                throw new \LogicException('Impossible de mélanger réponses séquentielles et réponses par chemin.');
            }

            if (is_int($key)) {
                $this->queue[] = $response;

                continue;
            }

            [$method, $pattern] = $this->parseKey($key);
            $this->expectations[] = ['method' => $method, 'pattern' => $pattern, 'response' => $response];
        }

        $this->mockClient = new MockClient([
            '*' => fn (PendingRequest $pendingRequest): MockResponse => $this->respond($pendingRequest),
        ]);
    }

    /**
     * @param array<int|string, MockedResponse> $responses
     */
    public static function create(array $responses = []): self
    {
        return new self($responses);
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function json(array $body, int $status = 200): MockedResponse
    {
        return new MockedResponse($status, $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function error(int $status, array $body = []): MockedResponse
    {
        return new MockedResponse($status, $body);
    }

    public function pennylane(): Pennylane
    {
        $pennylane = new Pennylane('mock-token');
        $pennylane->connector()->withMockClient($this->mockClient);

        return $pennylane;
    }

    public function firm(): PennylaneFirm
    {
        $firm = new PennylaneFirm('mock-token');
        $firm->connector()->withMockClient($this->mockClient);

        return $firm;
    }

    public function assertSent(string $method, string $path): void
    {
        $found = false;

        foreach ($this->recorded as $index => $call) {
            if ($this->matches($method, $path, $call['method'], $call['path'])) {
                $this->recorded[$index]['asserted'] = true;
                $found = true;
            }
        }

        Assert::assertTrue($found, sprintf(
            "Aucune requête %s %s envoyée. Requêtes vues :\n%s",
            strtoupper($method),
            $path,
            $this->recordedSummary(),
        ));
    }

    public function assertNotSent(string $method, string $path): void
    {
        $matched = array_values(array_filter(
            $this->recorded,
            fn (array $call): bool => $this->matches($method, $path, $call['method'], $call['path']),
        ));

        Assert::assertSame([], $matched, sprintf(
            'Une requête %s %s a été envoyée.',
            strtoupper($method),
            $path,
        ));
    }

    public function assertNothingSent(): void
    {
        Assert::assertSame([], $this->recorded, sprintf(
            "Des requêtes ont été envoyées :\n%s",
            $this->recordedSummary(),
        ));
    }

    /**
     * Chaque requête enregistrée doit avoir été couverte par un `assertSent()`
     * antérieur : le filet qui révèle les appels que le test ne décrit pas.
     */
    public function assertNothingElseSent(): void
    {
        $unasserted = array_values(array_filter(
            $this->recorded,
            static fn (array $call): bool => !$call['asserted'],
        ));

        Assert::assertSame([], $unasserted, sprintf(
            "Des requêtes n'ont fait l'objet d'aucun assertSent() :\n%s",
            implode("\n", array_map(
                static fn (array $call): string => sprintf('- %s %s', $call['method'], $call['path']),
                $unasserted,
            )),
        ));
    }

    private function respond(PendingRequest $pendingRequest): MockResponse
    {
        $method = $pendingRequest->getMethod()->value;
        $path = $this->normalizePath($pendingRequest->getUrl());

        $this->recorded[] = ['method' => $method, 'path' => $path, 'asserted' => false];

        if ($this->sequential) {
            $next = array_shift($this->queue);

            if (!$next instanceof MockedResponse) {
                throw new UnexpectedRequestException($method, $path);
            }

            return MockResponse::make($next->body, $next->status);
        }

        foreach ($this->expectations as $expectation) {
            if ($this->matches($expectation['method'], $expectation['pattern'], $method, $path)) {
                return MockResponse::make($expectation['response']->body, $expectation['response']->status);
            }
        }

        throw new UnexpectedRequestException($method, $path);
    }

    private function matches(string $expectedMethod, string $pattern, string $actualMethod, string $actualPath): bool
    {
        return strtoupper($expectedMethod) === $actualMethod && fnmatch($pattern, $actualPath);
    }

    private function normalizePath(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        foreach (self::BASE_PATHS as $basePath) {
            if (str_starts_with($path, $basePath)) {
                $relative = substr($path, strlen($basePath));

                return '' === $relative ? '/' : $relative;
            }
        }

        return $path;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseKey(string $key): array
    {
        $parts = preg_split('/\s+/', trim($key), 2);

        if (!is_array($parts) || 2 !== count($parts) || !str_starts_with($parts[1], '/')) {
            throw new \LogicException(sprintf('Clé de réponse invalide : « %s » (attendu : « METHODE /chemin », ex. « GET /journals/42 »).', $key));
        }

        return [strtoupper($parts[0]), $parts[1]];
    }

    private function recordedSummary(): string
    {
        if ([] === $this->recorded) {
            return '(aucune)';
        }

        return implode("\n", array_map(
            static fn (array $call): string => sprintf('- %s %s', $call['method'], $call['path']),
            $this->recorded,
        ));
    }
}
