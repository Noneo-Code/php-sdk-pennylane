<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

use Saloon\Http\Response;

abstract class PennylaneException extends \RuntimeException
{
    public function __construct(
        private readonly string $error,
        int $status,
        private readonly Response $response,
    ) {
        parent::__construct($error, $status);
    }

    public static function fromResponse(Response $response): ?self
    {
        $status = $response->status();
        $body = $response->json();
        $error = is_string($body['error'] ?? null) ? $body['error'] : $response->body();

        return match (true) {
            401 === $status => new UnauthorizedException($error, $status, $response),
            403 === $status => new ForbiddenException($error, $status, $response),
            404 === $status => new NotFoundException($error, $status, $response),
            409 === $status => new ConflictException($error, $status, $response),
            422 === $status => new ValidationException($error, $status, $response),
            429 === $status => new RateLimitException($error, $status, $response),
            in_array($status, [500, 503], true) => new ServerException($error, $status, $response),
            default => null,
        };
    }

    public function error(): string
    {
        return $this->error;
    }

    public function message(): string
    {
        return $this->error;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return [];
    }

    public function response(): Response
    {
        return $this->response;
    }
}
