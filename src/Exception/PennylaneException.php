<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

use Saloon\Http\Response;

/**
 * L'exception ne retient **aucune** référence à l'objet `Response` de Saloon :
 * depuis lui on remonte au `PendingRequest`, donc à ses en-têtes, donc au
 * `Authorization: Bearer …`. Un `Sentry::captureException()`, un `print_r()` ou
 * une simple trace suffiraient alors à journaliser le jeton. Seules les données
 * de diagnostic sont recopiées : statut, en-têtes de réponse filtrés, corps
 * tronqué et champs décodés.
 */
abstract class PennylaneException extends \RuntimeException
{
    /**
     * Champs du corps d'erreur qui portent le texte plutôt qu'un détail
     * structuré : ils alimentent error(), pas details().
     */
    private const TEXT_KEYS = ['error', 'message', 'status'];

    /**
     * Un 502 rendu par un proxy en amont porte une page HTML qui peut peser
     * des centaines de kilooctets : conservée telle quelle, elle transiterait
     * dans chaque log et chaque événement Sentry.
     */
    private const MAX_BODY_LENGTH = 2_000;

    private const MAX_MESSAGE_LENGTH = 500;

    /**
     * Allowlist et non denylist : les en-têtes utiles au diagnostic sont
     * connus et peu nombreux, alors qu'un en-tête inattendu peut porter un
     * secret — `Set-Cookie` au premier chef. Comparaison insensible à la
     * casse ; la clé restituée est celle listée ici.
     *
     * @var list<string>
     */
    private const DIAGNOSTIC_HEADERS = [
        'Retry-After',
        'Content-Type',
        'Date',
        'Location',
        'X-Request-Id',
        'X-Correlation-Id',
        'RateLimit-Limit',
        'RateLimit-Remaining',
        'RateLimit-Reset',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ];

    /**
     * @param array<string, mixed>  $details
     * @param array<string, string> $responseHeaders
     */
    public function __construct(
        private readonly string $error,
        int $status,
        private readonly array $details = [],
        private readonly array $responseHeaders = [],
        private readonly string $responseBody = '',
    ) {
        parent::__construct($error, $status);
    }

    /**
     * `$response` est marquée sensible : elle donne accès au `PendingRequest`
     * et à sa requête PSR signée. L'attribut retire cette frame des arguments
     * de trace quand l'hôte les conserve.
     */
    public static function fromResponse(#[\SensitiveParameter] Response $response): ?self
    {
        $status = $response->status();
        $body = self::decodeBody($response);
        $error = self::truncate(self::extractError($body, $response), self::MAX_MESSAGE_LENGTH);
        $details = self::extractDetails($body);
        $headers = self::extractHeaders($response);
        $rawBody = self::truncate($response->body(), self::MAX_BODY_LENGTH);

        return match (true) {
            400 === $status => new BadRequestException($error, $status, $details, $headers, $rawBody),
            401 === $status => new UnauthorizedException($error, $status, $details, $headers, $rawBody),
            403 === $status => new ForbiddenException($error, $status, $details, $headers, $rawBody),
            404 === $status => new NotFoundException($error, $status, $details, $headers, $rawBody),
            409 === $status => new ConflictException($error, $status, $details, $headers, $rawBody),
            422 === $status => new ValidationException($error, $status, $details, $headers, $rawBody),
            429 === $status => new RateLimitException($error, $status, $details, $headers, $rawBody),
            $status >= 500 => new ServerException($error, $status, $details, $headers, $rawBody),
            $status >= 400 => new ClientException($error, $status, $details, $headers, $rawBody),
            $status >= 300 => new RedirectException($error, $status, $details, $headers, $rawBody),
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

    /**
     * Statut HTTP de la réponse en échec. Également porté par `getCode()`, dont
     * il est ici le seul usage.
     */
    public function status(): int
    {
        return $this->getCode();
    }

    /**
     * En-têtes de réponse retenus pour le diagnostic, clés en casse d'origine.
     *
     * @return array<string, string>
     */
    public function responseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * Corps brut de la réponse, tronqué.
     */
    public function responseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * Un 5xx renvoyé par un proxy en amont de Pennylane porte une page HTML,
     * pas du JSON. Le corps est alors inexploitable, mais le statut et le corps
     * brut restent le seul diagnostic disponible : ils ne doivent pas être
     * perdus derrière une JsonException.
     *
     * @return array<array-key, mixed>
     */
    private static function decodeBody(#[\SensitiveParameter] Response $response): array
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
    private static function extractError(array $body, #[\SensitiveParameter] Response $response): string
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

    /**
     * @return array<string, string>
     */
    private static function extractHeaders(#[\SensitiveParameter] Response $response): array
    {
        $received = [];

        foreach ($response->headers()->all() as $name => $value) {
            $received[strtolower($name)] = self::stringifyHeader($value);
        }

        $headers = [];

        foreach (self::DIAGNOSTIC_HEADERS as $name) {
            $value = $received[strtolower($name)] ?? null;

            if (null !== $value) {
                $headers[$name] = self::truncate($value, self::MAX_MESSAGE_LENGTH);
            }
        }

        return $headers;
    }

    /**
     * PSR-7 autorise plusieurs valeurs pour un même en-tête ; Saloon les rend
     * telles quelles. Tout ce qui n'est pas représentable en texte est écarté
     * plutôt que rendu sous forme de `Array` ou d'`Object`.
     */
    private static function stringifyHeader(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map(self::stringifyHeader(...), $value));
        }

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * `mb_strcut` plutôt que `substr` : la coupe tombe sur une frontière de
     * caractère, si bien qu'un corps UTF-8 tronqué reste encodable en JSON.
     * Sur du binaire, elle se comporte comme `substr`.
     */
    private static function truncate(string $value, int $maxLength): string
    {
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_strcut($value, 0, $maxLength, 'UTF-8');
    }
}
