<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use GuzzleHttp\RequestOptions;
use Noneo\Sdk\Pennylane\Exception\InsecureBaseUrlException;
use Noneo\Sdk\Pennylane\Pennylane;
use Saloon\Http\PendingRequest;

/**
 * Invariants de transport communs à tous les connecteurs du SDK :
 *
 * - **HTTPS obligatoire.** Les `resolveBaseUrl()` sont en dur et en HTTPS, donc
 *   le risque est nul aujourd'hui ; le garde-fou existe pour le jour où une
 *   surcharge de base URL sera introduite (sandbox, tests d'intégration).
 *   Dérogation aux hôtes de bouclage, seuls endroits où le trafic ne quitte
 *   jamais la machine.
 * - **Pas de redirection suivie.** Une API JSON ne redirige pas ; suivre un 301
 *   rejouerait l'en-tête `Authorization` vers l'hôte annoncé par la réponse.
 * - **User-Agent identifiant.** Utile au support Pennylane pour repérer nos
 *   appels dans leurs journaux.
 */
trait SecuresPennylaneTransport
{
    /**
     * @var list<string>
     */
    private const LOOPBACK_HOSTS = ['localhost', '127.0.0.1', '::1', '[::1]'];

    public function bootSecuresPennylaneTransport(PendingRequest $pendingRequest): void
    {
        $url = $pendingRequest->getUrl();
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ('https' === $scheme) {
            return;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (!in_array($host, self::LOOPBACK_HOSTS, true)) {
            throw new InsecureBaseUrlException($url);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [RequestOptions::ALLOW_REDIRECTS => false];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultHeaders(): array
    {
        return ['User-Agent' => 'noneo-sdk-pennylane/'.Pennylane::VERSION];
    }
}
