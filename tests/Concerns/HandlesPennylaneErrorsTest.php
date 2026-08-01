<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Concerns;

use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Exception\ConflictException;
use Noneo\Sdk\Pennylane\Exception\ForbiddenException;
use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\Exception\RateLimitException;
use Noneo\Sdk\Pennylane\Exception\ServerException;
use Noneo\Sdk\Pennylane\Exception\UnauthorizedException;
use Noneo\Sdk\Pennylane\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class HandlesPennylaneErrorsTest extends TestCase
{
    public function testThrowsUnauthorizedExceptionOn401(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'The access token is invalid', 'status' => 401],
            401,
        ));

        self::assertInstanceOf(UnauthorizedException::class, $exception);
        self::assertSame('The access token is invalid', $exception->error());
        self::assertSame('The access token is invalid', $exception->message());
        self::assertSame([], $exception->details());
        self::assertSame(401, $exception->response()->status());
    }

    public function testThrowsForbiddenExceptionOn403WithMissingScope(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Access to this resource requires scope "ledger".', 'status' => 403],
            403,
        ));

        self::assertInstanceOf(ForbiddenException::class, $exception);
        self::assertSame('ledger', $exception->missingScope());
    }

    public function testForbiddenExceptionMissingScopeReturnsNullWhenNotEmbedded(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Access to this resource forbidden', 'status' => 403],
            403,
        ));

        self::assertInstanceOf(ForbiddenException::class, $exception);
        self::assertNull($exception->missingScope());
    }

    public function testThrowsNotFoundExceptionOn404(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Not Found', 'status' => 404],
            404,
        ));

        self::assertInstanceOf(NotFoundException::class, $exception);
        self::assertSame('Not Found', $exception->error());
    }

    public function testThrowsConflictExceptionOn409(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Conflict with the current state of the target resource. Resource already exists.', 'status' => 409],
            409,
        ));

        self::assertInstanceOf(ConflictException::class, $exception);
        self::assertNull($exception->existingId());
    }

    public function testThrowsValidationExceptionOn422(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Entry lines are not balanced', 'status' => 422],
            422,
        ));

        self::assertInstanceOf(ValidationException::class, $exception);
        self::assertSame('Entry lines are not balanced', $exception->error());
        self::assertNull($exception->errorCode());
    }

    public function testThrowsRateLimitExceptionOn429(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Too Many Requests', 'status' => 429],
            429,
            ['Retry-After' => '3'],
        ));

        self::assertInstanceOf(RateLimitException::class, $exception);
        self::assertSame(3, $exception->retryAfter());
    }

    public function testThrowsServerExceptionOn500(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Internal server error', 'status' => 500],
            500,
        ));

        self::assertInstanceOf(ServerException::class, $exception);
    }

    public function testThrowsServerExceptionOn503(): void
    {
        $exception = $this->sendAndCapture(MockResponse::make(
            ['error' => 'Service unavailable', 'status' => 503],
            503,
        ));

        self::assertInstanceOf(ServerException::class, $exception);
    }

    private function sendAndCapture(MockResponse $response): \Throwable
    {
        $connector = new class extends Connector {
            use HandlesPennylaneErrors;

            public function resolveBaseUrl(): string
            {
                return 'https://app.pennylane.com/api/external/v2';
            }
        };

        $connector->withMockClient(new MockClient([$response]));

        try {
            $connector->send(new class extends Request {
                protected Method $method = Method::GET;

                public function resolveEndpoint(): string
                {
                    return '/ledger_entries';
                }
            });

            self::fail('Expected an exception to be thrown.');
        } catch (\Throwable $exception) {
            return $exception;
        }
    }
}
