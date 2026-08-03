<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

/**
 * Télécharge une spec OpenAPI publiée par Pennylane et refuse tout ce qui n'en
 * est pas une : le fichier obtenu écrase la spec committée, qui sert de source
 * de vérité au SDK et à l'audit de couverture.
 */
final class SpecDownloader
{
    public function __construct(private readonly HttpFetcher $fetcher)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function download(string $url): array
    {
        $body = $this->fetcher->get($url);

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Réponse non JSON pour {$url} : {$e->getMessage()}", 0, $e);
        }

        if (!is_array($decoded) || !isset($decoded['paths']) || !is_array($decoded['paths']) || [] === $decoded['paths']) {
            throw new \RuntimeException("Spec sans opération pour {$url} : le document ne porte aucun `paths`.");
        }

        /** @var array<string, mixed> $spec */
        $spec = $decoded;

        return $spec;
    }
}
