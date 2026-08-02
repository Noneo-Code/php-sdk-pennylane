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

## Démarrage

Le connecteur accepte n'importe quel `Saloon\Contracts\Authenticator`. Les
méthodes de ressource rendent des DTO, jamais une réponse brute.

```php
use Noneo\Sdk\Pennylane\Pennylane;
use Saloon\Http\Auth\TokenAuthenticator;

$pennylane = new Pennylane(new TokenAuthenticator($companyToken));

$invoice = $pennylane->customerInvoices()->get(42);
echo $invoice->amount;
```

Sous Symfony, le connecteur se déclare comme n'importe quel service :

```yaml
# services.yaml
Noneo\Sdk\Pennylane\Pennylane:
    arguments:
        $authenticator: '@app.pennylane.authenticator'
```

## Pagination

Deux entrées, selon que vous pilotez le curseur ou non :

```php
// Une page, curseur à votre main
$page = $pennylane->customerInvoices()->list(limit: 50);
$page->items;      // CustomerInvoiceDto[]
$page->hasMore;    // bool
$page->nextCursor; // ?string

// Toutes les pages, en Generator
foreach ($pennylane->customerInvoices()->paginate() as $invoice) {
    // …
}
```

`paginate()` laisse par défaut Pennylane choisir la taille de page (20). Sur
de gros volumes, demandez-en davantage — la borne haute dépend de l'endpoint
(100 le plus souvent, 1000 pour quelques-uns) :

```php
foreach ($pennylane->ledgerEntries()->paginate(perPage: 100) as $entry) {
    // 5 fois moins de requêtes, donc 5 fois moins de temps sous le quota
}
```

## Filtres et tri

```php
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;

$page = $pennylane->customerInvoices()->list(
    filters: FilterSet::make()
        ->gteq('date', '2026-01-01')
        ->in('customer_id', [12, 34]),
    sort: Sort::desc('date'),
);
```

Les neuf opérateurs de `FilterOperator` sont typés : une faute de frappe est
une erreur de compilation, pas un 422 en production. `paginate()` renvoie
filtre et tri à chaque page — sans quoi Pennylane retourne silencieusement les
pages suivantes non filtrées.

## Mises à jour partielles

Un champ laissé à `null` n'est pas envoyé : Pennylane conserve sa valeur.
Pour **vider** un champ, passez `Clear::value()`, qui envoie un `null`
explicite.

```php
use Noneo\Sdk\Pennylane\Http\Clear;

$pennylane->suppliers()->update(
    id: 42,
    notes: Clear::value(), // "notes": null — le champ est vidé
    phone: null,           // absent du corps — le téléphone est conservé
);
```

(`Unset` aurait été le nom naturel, mais c'est un mot réservé du langage.)

## Firm API

`PennylaneFirm` cible le cabinet ; `company()` rend un scope dont les requêtes
partent sur `/companies/{id}/…`. Le préfixe est porté par le connecteur, donc
une même instance de requête peut être réutilisée, y compris d'une entreprise
à l'autre.

```php
use Noneo\Sdk\Pennylane\PennylaneFirm;

$firm = new PennylaneFirm(new TokenAuthenticator($firmToken));
$firm->company(1234)->send(new GetLedgerEntries());
```

## OAuth 2.0

`Auth\OAuthClient` ne fait que le ballet OAuth et rend un authenticator à
passer au connecteur métier :

```php
use Noneo\Sdk\Pennylane\Auth\OAuthClient;

$oauth = new OAuthClient($clientId, $clientSecret, $redirectUri);

// 1. Rediriger l'utilisateur
$url = $oauth->getAuthorizationUrl(scopes: ['customer_invoices:all'], state: $state);

// 2. Échanger le code au retour
$authenticator = $oauth->getAccessToken($code);

// 3. Rafraîchir si besoin, puis consommer
$pennylane = new Pennylane($oauth->refreshIfExpired($authenticator));
```

## Développement

```bash
composer install
composer check   # test + stan + cs, comme en CI
```

Scripts disponibles :

- `composer test` — suite PHPUnit
- `composer stan` — PHPStan niveau `max` (`bin`, `src`, `tests`)
- `composer cs` — vérification PHP-CS-Fixer (`@Symfony`, mode `--dry-run`)
- `composer check` — enchaîne les trois
- `php bin/coverage.php` — couverture des endpoints face à la spec OpenAPI

`bin/coverage.php` rapproche chaque opération de la spec d'une classe `Request`
sur **la méthode HTTP et le chemin**, pas sur le nom de classe : une requête
bien nommée qui tape la mauvaise URL est signalée, et les `operationId` hors
convention (`company-fiscal-years`) ne produisent plus de faux manquants. Les
requêtes sans opération documentée correspondante sont listées à part.

## Limites connues

- Le paramètre `include` de `GET /customer_invoices` n'est pas exposé : il est
  marqué expérimental par Pennylane et sa réponse place les ressources liées
  dans une section `included` que `CursorPage` ne modélise pas. L'exposer
  seul rendrait un résultat inaccessible.
- La borne haute de `limit` n'est pas validée localement : elle vaut 100 sur la
  plupart des endpoints mais 1000 sur quatorze d'entre eux. Un dépassement
  remonte en `BadRequestException`.

## Erreurs et rate limit

Les erreurs Pennylane sont exposées via une hiérarchie d'exceptions dédiée,
jamais la `RequestException` générique de Saloon :

| Statut | Exception | Accesseurs |
|---|---|---|
| 400 | `BadRequestException` | `errorCode()`, `details()` |
| 401 | `UnauthorizedException` | |
| 403 | `ForbiddenException` | `missingScope()` |
| 404 | `NotFoundException` | |
| 409 | `ConflictException` | `existingId()` |
| 422 | `ValidationException` | |
| 429 | `RateLimitException` | `retryAfter()` |
| autre 4xx | `ClientException` | |
| 5xx | `ServerException` | |

Toutes héritent de `PennylaneException`, qui expose `error()`, `message()`,
`errorCode()`, `details()` et la `Response` d'origine. Un `catch` sur la classe
mère couvre donc toute la surface d'erreur, y compris une page HTML renvoyée
par un proxy en amont sur un 502 : le corps illisible n'efface ni le statut ni
le corps brut.

```php
try {
    $pennylane->customerInvoices()->get(42);
} catch (Noneo\Sdk\Pennylane\Exception\NotFoundException $e) {
    // $e->error(), $e->response()
} catch (Noneo\Sdk\Pennylane\Exception\PennylaneException $e) {
    // tout le reste
}
```

Pennylane limite à 25 requêtes par tranche de 5 secondes, **par token** — donc
partagé entre tous les process qui l'utilisent. Le SDK segmente son compteur
par jeton (haché), afin que deux entreprises partageant un même store ne se
disputent pas un unique quota.

Par défaut, le SDK suit ce quota en mémoire (`MemoryStore`), ce qui ne suffit
plus dès qu'un jeton est partagé entre plusieurs process. Injectez alors un
store PSR-16 partagé, par exemple depuis Symfony :

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
