<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Webhook;

use Noneo\Sdk\Pennylane\Exception\InvalidWebhookSignatureException;

/**
 * Pennylane ne documente pas le format de signature de ses webhooks (bêta) et
 * les intégrations existantes ont fait des paris incompatibles. Le SDK ne
 * tranche donc pas à la place de l'API : il porte les deux schémas connus
 * (cf. `WebhookSignatureScheme`) et retient par défaut le seul qui protège du
 * rejeu. Le schéma se choisit à la construction, jamais d'après l'en-tête reçu.
 *
 * Le format réel reste à confirmer par la capture d'un webhook authentique.
 */
final readonly class SignatureVerifier
{
    /**
     * Nom de l'en-tête portant la signature. Pennylane ne le documente pas
     * publiquement (webhooks en bêta) ; cette valeur reste celle observée et
     * reprise par les intégrations tierces.
     */
    public const string HEADER = 'X-Pennylane-Signature';

    public function __construct(
        #[\SensitiveParameter] private string $secret,
        private int $toleranceSeconds = 300,
        private WebhookSignatureScheme $scheme = WebhookSignatureScheme::Timestamped,
    ) {
    }

    public function verify(string $rawBody, string $signatureHeader): bool
    {
        return match ($this->scheme) {
            WebhookSignatureScheme::Timestamped => $this->verifyTimestamped($rawBody, $signatureHeader),
            WebhookSignatureScheme::BodyOnly => $this->verifyBodyOnly($rawBody, $signatureHeader),
        };
    }

    /**
     * Vérifie **puis** décode : la seule voie qui rend impossible de traiter un
     * événement dont la signature n'a pas été contrôlée.
     *
     * @throws InvalidWebhookSignatureException si la signature ne correspond pas
     * @throws \JsonException                   si le corps n'est pas du JSON valide
     * @throws \InvalidArgumentException        si l'enveloppe ne porte pas les champs attendus
     */
    public function constructEvent(string $rawBody, string $signatureHeader): WebhookEvent
    {
        if (!$this->verify($rawBody, $signatureHeader)) {
            throw new InvalidWebhookSignatureException();
        }

        $payload = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Le corps d\'un webhook Pennylane doit être un objet JSON.');
        }

        return WebhookEvent::fromArray($payload);
    }

    private function verifyTimestamped(string $rawBody, string $signatureHeader): bool
    {
        $parsed = self::parseHeader($signatureHeader);

        if (null === $parsed) {
            return false;
        }

        [$timestamp, $signature] = $parsed;

        if (abs(time() - $timestamp) > $this->toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $this->secret);

        return hash_equals($expected, $signature);
    }

    private function verifyBodyOnly(string $rawBody, string $signatureHeader): bool
    {
        $candidate = self::decodeSignature($signatureHeader);

        if (null === $candidate) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $this->secret, binary: true), $candidate);
    }

    /**
     * Rend les 32 octets bruts de la signature, quel que soit l'encodage
     * employé par l'émetteur, ou `null` si l'en-tête n'en porte pas.
     */
    private static function decodeSignature(string $signatureHeader): ?string
    {
        $value = trim($signatureHeader);
        $value = (string) preg_replace('/^(?:hmac-)?sha256=/i', '', $value);

        if (1 === preg_match('/^[0-9a-f]{64}$/i', $value)) {
            return hex2bin(strtolower($value)) ?: null;
        }

        $decoded = base64_decode($value, strict: true);

        return is_string($decoded) && 32 === strlen($decoded) ? $decoded : null;
    }

    /** @return array{0: int, 1: string}|null */
    private static function parseHeader(string $signatureHeader): ?array
    {
        $parts = [];

        foreach (explode(',', $signatureHeader) as $pair) {
            $segments = explode('=', $pair, 2);

            if (2 !== count($segments)) {
                return null;
            }

            [$key, $value] = $segments;
            $parts[$key] = $value;
        }

        if (!isset($parts['t'], $parts['v1']) || 1 !== preg_match('/^\d+$/', $parts['t'])) {
            return null;
        }

        return [(int) $parts['t'], $parts['v1']];
    }
}
