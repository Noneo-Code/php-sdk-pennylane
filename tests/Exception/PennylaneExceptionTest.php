<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Exception;

use Noneo\Sdk\Pennylane\Exception\PennylaneException;
use Noneo\Sdk\Pennylane\Exception\ServerException;
use Noneo\Sdk\Pennylane\Exception\UnauthorizedException;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class PennylaneExceptionTest extends TestCase
{
    private const TOKEN = 'super-secret-bearer-token';

    private string $ignoreArgs;

    protected function setUp(): void
    {
        $this->ignoreArgs = (string) ini_get('zend.exception_ignore_args');
    }

    protected function tearDown(): void
    {
        ini_set('zend.exception_ignore_args', $this->ignoreArgs);
    }

    /**
     * Le cœur du durcissement : l'exception ne retient plus rien de Saloon,
     * donc plus aucun chemin d'objet ne mène de l'exception au jeton.
     */
    public function testTheExceptionHoldsNoReferenceToASaloonObject(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'nope', 'status' => 401], 401));

        foreach ((new \ReflectionObject($exception))->getProperties() as $property) {
            $value = $property->isInitialized($exception) ? $property->getValue($exception) : null;

            self::assertFalse(
                is_object($value) && str_starts_with($value::class, 'Saloon\\'),
                sprintf('La propriété %s retient un objet Saloon (%s).', $property->getName(), get_debug_type($value)),
            );
        }
    }

    /**
     * `zend.exception_ignore_args=1` — le réglage de `php.ini-production` — est
     * l'invariant d'exploitation documenté : sous lui, plus rien ne porte le
     * jeton, ni l'exception ni sa trace.
     */
    public function testNothingCarriesTheBearerTokenWhenTraceArgumentsAreIgnored(): void
    {
        ini_set('zend.exception_ignore_args', '1');

        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'nope', 'status' => 401], 401));

        self::assertStringNotContainsString(self::TOKEN, print_r($exception, true));
        self::assertStringNotContainsString(self::TOKEN, print_r($exception->getTrace(), true));
        self::assertStringNotContainsString(self::TOKEN, $exception->getTraceAsString());
    }

    /**
     * La trace textuelle est ce qui atterrit dans les logs : elle ne rend que
     * les *types* des arguments, jamais leurs valeurs — vrai même quand les
     * arguments de trace sont conservés.
     */
    public function testTheTextualTraceNeverCarriesTheBearerToken(): void
    {
        ini_set('zend.exception_ignore_args', '0');

        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'nope', 'status' => 401], 401));

        self::assertStringNotContainsString(self::TOKEN, $exception->getTraceAsString());
    }

    /**
     * Quand l'hôte conserve les arguments de trace, les frames de Saloon
     * exposent la `Response` — et par elle la requête PSR signée, dont le
     * `psrRequest` est `readonly` et hors de notre portée. Les nôtres, elles,
     * sont neutralisées par `#[\SensitiveParameter]`.
     */
    public function testOurOwnFramesCarryNoBearerTokenEvenWhenTraceArgumentsAreKept(): void
    {
        ini_set('zend.exception_ignore_args', '0');

        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'nope', 'status' => 401], 401));
        $ours = array_filter(
            $exception->getTrace(),
            static fn (array $frame): bool => str_starts_with($frame['class'] ?? '', 'Noneo\\Sdk\\Pennylane\\'),
        );

        self::assertNotEmpty($ours, 'La trace devrait traverser au moins une frame du SDK.');

        foreach ($ours as $frame) {
            self::assertStringNotContainsString(
                self::TOKEN,
                print_r($frame['args'] ?? [], true),
                sprintf('La frame %s%s%s expose le jeton.', $frame['class'], $frame['type'] ?? '::', $frame['function']),
            );
        }
    }

    public function testTruncatesAHugeErrorBody(): void
    {
        $body = str_repeat('a', 100_000);

        $exception = $this->sendAndCapture(MockResponse::make($body, 502, ['Content-Type' => 'text/html']));

        self::assertInstanceOf(ServerException::class, $exception);
        self::assertSame(2_000, strlen($exception->responseBody()));
        self::assertSame(str_repeat('a', 2_000), $exception->responseBody());
    }

    public function testTruncatesAHugeErrorMessage(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => str_repeat('b', 10_000)],
            500,
        ));

        self::assertSame(500, strlen($exception->error()));
        self::assertSame(500, strlen($exception->getMessage()));
    }

    /**
     * Tronquer à l'octet couperait un caractère multioctet en deux et rendrait
     * une chaîne UTF-8 invalide — impossible à écrire dans un log JSON.
     */
    public function testTruncationNeverSplitsAMultibyteCharacter(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(str_repeat('é', 50_000), 500, ['Content-Type' => 'text/plain']));

        self::assertLessThanOrEqual(2_000, strlen($exception->responseBody()));
        self::assertTrue(mb_check_encoding($exception->responseBody(), 'UTF-8'));
    }

    public function testKeepsAShortBodyIntact(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'Not Found', 'status' => 404], 404));

        self::assertSame('{"error":"Not Found","status":404}', $exception->responseBody());
    }

    public function testExposesTheHttpStatus(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'nope', 'status' => 401], 401));

        self::assertInstanceOf(UnauthorizedException::class, $exception);
        self::assertSame(401, $exception->status());
    }

    public function testKeepsTheDiagnosticResponseHeaders(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Too Many Requests'],
            429,
            ['Retry-After' => '3', 'X-Request-Id' => 'req-42', 'Content-Type' => 'application/json'],
        ));

        self::assertSame('3', $exception->responseHeaders()['Retry-After'] ?? null);
        self::assertSame('req-42', $exception->responseHeaders()['X-Request-Id'] ?? null);
        self::assertSame('application/json', $exception->responseHeaders()['Content-Type'] ?? null);
    }

    /**
     * Allowlist et non denylist : un en-tête inconnu peut porter un secret
     * (`Set-Cookie` de session au premier chef), et on ne devine pas.
     */
    public function testDropsResponseHeadersOutsideTheDiagnosticAllowlist(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'nope'],
            401,
            ['Set-Cookie' => 'session=secret-session-value', 'Authorization' => 'Bearer echoed-token'],
        ));

        $rendered = print_r($exception->responseHeaders(), true);

        self::assertStringNotContainsString('secret-session-value', $rendered);
        self::assertStringNotContainsString('echoed-token', $rendered);
        self::assertArrayNotHasKey('Set-Cookie', $exception->responseHeaders());
    }

    public function testHeaderLookupIsCaseInsensitive(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(['error' => 'nope'], 429, ['retry-after' => '7']));

        self::assertSame('7', $exception->responseHeaders()['Retry-After'] ?? null);
    }

    private function sendAndCapture(MockResponse $response): PennylaneException
    {
        $pennylane = new Pennylane(new TokenAuthenticator(self::TOKEN), maxRetries: 0);
        $pennylane->withMockClient(new MockClient([$response]));

        try {
            $pennylane->send(new class extends Request {
                protected Method $method = Method::GET;

                public function resolveEndpoint(): string
                {
                    return '/ledger_entries';
                }
            });

            self::fail('Expected an exception to be thrown.');
        } catch (PennylaneException $exception) {
            return $exception;
        }
    }
}
