<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http\Connector;

use Psr\SimpleCache\CacheInterface;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;

/**
 * Store de limitation de débit adossé à un cache PSR-16, aux clés conformes.
 *
 * Le `PsrStore` de saloonphp/rate-limit-plugin transmet tel quel le nom de la
 * limite (`pennylane:<identité>:25_every_5`). Or PSR-16 réserve `{}()/\@:` et
 * ne garantit que `A-Z a-z 0-9 _ .` sur 64 caractères : un cache strict (celui
 * de Symfony, par exemple) refuse la clé et chaque requête échoue avant même
 * d'atteindre Pennylane. Ce store remplace tout caractère hors de cet
 * ensemble par un point, et condense par empreinte une clé trop longue.
 */
final class Psr16RateLimitStore implements RateLimitStore
{
    private const int LONGUEUR_MAXIMALE = 64;

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function get(string $key): ?string
    {
        $value = $this->cache->get(self::cle($key));

        return \is_string($value) ? $value : null;
    }

    public function set(string $key, string $value, int $ttl): bool
    {
        return $this->cache->set(self::cle($key), $value, $ttl);
    }

    /**
     * Clé PSR-16 portable dérivée du nom de limite.
     */
    public static function cle(string $key): string
    {
        $cle = (string) preg_replace('/[^A-Za-z0-9_.]/', '.', $key);

        if (\strlen($cle) <= self::LONGUEUR_MAXIMALE) {
            return $cle;
        }

        // 10 + 32 caractères : sous la limite de 64.
        return 'pennylane.'.hash('xxh128', $key);
    }
}
