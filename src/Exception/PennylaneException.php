<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

use Saloon\Http\Response;

abstract class PennylaneException extends \RuntimeException
{
    /**
     * Champs du corps d'erreur qui portent le texte plutôt qu'un détail
     * structuré : ils alimentent error(), pas details().
     */
    private const TEXT_KEYS = ['error', 'message', 'status'];

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        private readonly string $error,
        int $status,
        private readonly Response $response,
        private readonly array $details = [],
    ) {
        parent::__construct($error, $status);
    }

    public static function fromResponse(Response $response): ?self
    {
        $status = $response->status();
        $body = self::decodeBody($response);
        $error = self::extractError($body, $response);
        $details = self::extractDetails($body);

        return match (true) {
            400 === $status => new BadRequestException($error, $status, $response, $details),
            401 === $status => new UnauthorizedException($error, $status, $response, $details),
            403 === $status => new ForbiddenException($error, $status, $response, $details),
            404 === $status => new NotFoundException($error, $status, $response, $details),
            409 === $status => new ConflictException($error, $status, $response, $details),
            422 === $status => new ValidationException($error, $status, $response, $details),
            429 === $status => new RateLimitException($error, $status, $response, $details),
            $status >= 500 => new ServerException($error, $status, $response, $details),
            $status >= 400 => new ClientException($error, $status, $response, $details),
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
     * Code d'erreur structuré, documenté sur les réponses 400 de validation de
     * schéma (`InvalidDateFormat`, `NotExistRequiredKey`, …).
     */
    public function errorCode(): ?string
    {
        $code = $this->details['code'] ?? null;

        return is_string($code) ? $code : null;
    }

    /**
     * Champs structurés du corps d'erreur, hors texte : `code`, `field`,
     * `payload` selon l'endpoint.
     *
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }

    public function response(): Response
    {
        return $this->response;
    }

    /**
     * Un 5xx renvoyé par un proxy en amont de Pennylane porte une page HTML,
     * pas du JSON. Le corps est alors inexploitable, mais le statut et le corps
     * brut restent le seul diagnostic disponible : ils ne doivent pas être
     * perdus derrière une JsonException.
     *
     * @return array<array-key, mixed>
     */
    private static function decodeBody(Response $response): array
    {
        try {
            return $response->json();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param array<array-key, mixed> $body
     */
    private static function extractError(array $body, Response $response): string
    {
        foreach (['error', 'message'] as $key) {
            $value = $body[$key] ?? null;

            if (is_string($value)) {
                return $value;
            }
        }

        return $response->body();
    }

    /**
     * @param array<array-key, mixed> $body
     *
     * @return array<string, mixed>
     */
    private static function extractDetails(array $body): array
    {
        $details = [];

        foreach (array_diff_key($body, array_flip(self::TEXT_KEYS)) as $key => $value) {
            if (is_string($key)) {
                $details[$key] = $value;
            }
        }

        return $details;
    }
}
