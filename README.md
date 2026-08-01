# noneo/sdk-pennylane

SDK PHP pour l'API [Pennylane](https://pennylane.readme.io) — Company API v2
et Firm API v1.

Le paquet est **agnostique de tout framework** : il ne dépend ni de Symfony,
ni de Laravel, et s'utilise par simple injection de dépendances.

## Installation

```bash
composer require noneo/sdk-pennylane
```

Requiert PHP `>=8.4`.

## Développement

```bash
composer install
composer check   # test + stan + cs, comme en CI
```

Scripts disponibles :

- `composer test` — suite PHPUnit
- `composer stan` — PHPStan niveau `max`
- `composer cs` — vérification PHP-CS-Fixer (`@Symfony`, mode `--dry-run`)
- `composer check` — enchaîne les trois

## Erreurs et rate limit

Les erreurs Pennylane sont exposées via une hiérarchie d'exceptions dédiée
(`Noneo\Sdk\Pennylane\Exception\PennylaneException` et ses sous-classes
`UnauthorizedException`, `ForbiddenException`, `NotFoundException`,
`ConflictException`, `ValidationException`, `RateLimitException`,
`ServerException`), plutôt que la `RequestException` générique de Saloon :

```php
try {
    $pennylane->customerInvoices()->get(42);
} catch (Noneo\Sdk\Pennylane\Exception\NotFoundException $e) {
    // $e->error(), $e->response()
}
```

Pennylane limite à 25 requêtes par tranche de 5 secondes, **par token** — donc
partagé entre tous les process qui l'utilisent. Par défaut, le SDK suit ce
quota en mémoire (`MemoryStore`), ce qui ne suffit plus dès qu'un jeton est
partagé entre plusieurs process. Injectez alors un store PSR-16 partagé, par
exemple depuis Symfony :

```php
use Saloon\RateLimitPlugin\Stores\PsrStore;
use Symfony\Component\Cache\Psr16Cache;

$pennylane = new Noneo\Sdk\Pennylane\Pennylane(
    $authenticator,
    new PsrStore(new Psr16Cache($cachePool)),
);
```

Un 429 ou une erreur 5xx accompagnée d'un en-tête `Retry-After` déclenche une
unique nouvelle tentative après le délai indiqué par Pennylane ; au-delà, une
`RateLimitException` (ou `ServerException`) est levée.

## Dépendances et Guzzle

Le SDK repose sur [Saloon](https://docs.saloon.dev/) v4, qui requiert
`guzzlehttp/guzzle` en dur. Un projet Symfony consommant ce SDK aura donc
Guzzle installé en plus de `symfony/http-client` — les deux coexistent sans
conflit, ce sont simplement deux clients HTTP présents sur le disque.

## Licence

MIT
