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

## Dépendances et Guzzle

Le SDK repose sur [Saloon](https://docs.saloon.dev/) v4, qui requiert
`guzzlehttp/guzzle` en dur. Un projet Symfony consommant ce SDK aura donc
Guzzle installé en plus de `symfony/http-client` — les deux coexistent sans
conflit, ce sont simplement deux clients HTTP présents sur le disque.

## Licence

MIT
