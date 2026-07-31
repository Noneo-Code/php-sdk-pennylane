# SDK PHP Pennylane — design

- **Date** : 2026-07-31
- **Paquet** : `noneo/sdk-pennylane`
- **Namespace** : `Noneo\Sdk\Pennylane\`
- **Dépôt** : `gitlab.noneo.fr/noneo/php-packages/php-sdk-pennylane`
- **Statut** : validé, prêt pour le plan d'implémentation

## 1. Objectif

Fournir un client PHP pour l'API Pennylane, couvrant à terme la Company API v2
(≈ 160 endpoints) et la Firm API v1 (≈ 44 endpoints), livré par lots successifs.

Le SDK est **agnostique de tout framework**. Il est écrit pour être consommé
depuis Symfony par injection de dépendances, sans jamais dépendre de Symfony
non plus.

## 2. L'API Pennylane en bref

| | Company API | Firm API |
|---|---|---|
| Hôte | `https://app.pennylane.com` | `https://app.pennylane.com` |
| Préfixe | `/api/external/v2` | `/api/external/firm/v1` |
| Portée | une entreprise | un cabinet, chemins préfixés `/companies/{company_id}` |
| Volume | ≈ 160 endpoints | ≈ 44 endpoints |
| Doc | `pennylane.readme.io` | `firm-pennylane.readme.io` |

**Authentification** : `Authorization: Bearer <token>`, avec trois origines
possibles pour le jeton — token Company, token Firm, ou OAuth 2.0
(authorization code + refresh). Chaque endpoint exige un ou plusieurs scopes
nommés (`customer_invoices:readonly`, `ledger_entries:all`, …).

**Pagination** : par curseur. `?cursor=<opaque>&limit=<n>` (défaut 20, maximum
variable selon l'endpoint). Réponse enveloppée :

```json
{ "items": [ … ], "has_more": true, "next_cursor": "eyJpZCI6MTAwfQ==" }
```

Contrainte documentée : les paramètres `filter` et `sort` doivent être renvoyés
**à chaque page**, sinon les pages suivantes reviennent non filtrées.

**Filtres** : `?filter=` reçoit un tableau JSON encodé d'objets
`{field, operator, value}`. Neuf opérateurs : `eq`, `not_eq`, `lt`, `lteq`,
`gt`, `gteq`, `in`, `not_in`, `start_with`.

**Rate limiting** : 25 requêtes par tranche de 5 secondes, **par token** (donc
partagé entre tous les process qui utilisent le même jeton). Dépassement →
`429` avec `retry-after`, `ratelimit-limit`, `ratelimit-remaining`,
`ratelimit-reset`. Les trois derniers sont présents aussi sur les réponses
réussies.

**Erreurs** : corps `{"error": "<code>", "message": "<texte>", "details": {…}}`.
Codes rencontrés : 400, 401, 403, 404, 409, 422, 429, 500, 503.

**Webhooks** : en-tête `X-Pennylane-Signature` au format `t=<unix>,v1=<hex>`,
avec `v1 = hmac_sha256("{t}.{corps_brut}", secret)`.

**Source de vérité** : chaque page `https://pennylane.readme.io/reference/<op>.md`
embarque son fragment OpenAPI 3.0.1. Une spec complète est donc reconstructible
localement.

## 3. Décisions d'architecture

### 3.1 Deux connecteurs, DTO partagés

`Pennylane` (Company v2) et `PennylaneFirm` (Firm v1) sont deux classes
distinctes. Les `Dto/` sont mutualisés — un `LedgerEntryDto` sert aux deux
APIs, dont les schémas de réponse coïncident. Les `Requests/` sont séparés par
connecteur, parce que les chemins diffèrent.

*Alternative écartée* : un connecteur unique avec `resolveBaseUrl()` dynamique.
Rejeté parce que les ressources deviendraient conditionnelles — `journals()`
existe des deux côtés, `quotes()` non — donc l'autocomplétion proposerait des
méthodes qui échouent à l'exécution.

### 3.2 L'authentification ne touche pas les connecteurs

Les connecteurs acceptent n'importe quel `Saloon\Contracts\Authenticator` :

```php
new Pennylane(new TokenAuthenticator($companyToken));
new PennylaneFirm(new TokenAuthenticator($firmToken));
new Pennylane($oauthClient->refreshIfExpired($storedToken));
```

`Auth\OAuthClient` est un connecteur séparé dont le seul rôle est le ballet
OAuth : `getAuthorizationUrl()`, `getAccessToken()`, `refreshAccessToken()`. Il
rend un `AccessTokenAuthenticator` que l'appelant passe au connecteur métier.

Conséquence : aucune branche d'authentification dans les 160 requêtes, et OAuth
se teste isolément.

### 3.3 Le scope entreprise du Firm est un décorateur

```php
$firm->companies()->list();                     // /companies
$firm->company(1234)->ledgerEntries()->list();  // /companies/1234/ledger_entries
```

`company(int $id)` rend un `CompanyScope` qui n'expose que les ressources
réellement disponibles côté Firm.

### 3.4 Les méthodes de ressources rendent des DTO

Rupture assumée avec `noneo-sdk-forwardemail`, qui rend du
`Saloon\Http\Response` brut et laisse l'appelant faire `->dto()`.

```php
$invoice = $pennylane->customerInvoices()->get(42);
$invoice->amount;
```

## 4. Arborescence

```
pennylane-api-v2.yaml          spec Company reconstruite (commitée)
pennylane-api-firm-v1.yaml     spec Firm reconstruite (commitée)
bin/fetch-openapi.php          scrape /reference/*.md, fusionne les fragments
bin/coverage.php               spec ↔ src/Requests → % couvert + manquants

src/
  Pennylane.php                connecteur Company v2
  PennylaneFirm.php            connecteur Firm v1
  CompanyScope.php             décorateur d'entreprise pour le Firm
  Auth/
    OAuthClient.php
  Http/
    CursorPaginator.php
    CursorPage.php
  Filter/
    Filter.php  FilterOperator.php  FilterSet.php  Sort.php
  Exception/
    PennylaneException.php  (abstraite)
    UnauthorizedException.php  ForbiddenException.php  NotFoundException.php
    ConflictException.php  ValidationException.php  RateLimitException.php
    ServerException.php
  Concerns/
    HandlesPennylaneErrors.php  RespectsPennylaneRateLimits.php
  Resource/                    ≈ 20 classes, une par groupe fonctionnel
  Requests/<Groupe>/           une classe par endpoint
  Dto/<Groupe>/                DTO de réponse, partagés entre connecteurs
  Webhook/
    SignatureVerifier.php  WebhookEvent.php

tests/                         miroir de src/, MockClient partout
```

## 5. Le socle transverse

### 5.1 Pagination

Deux entrées distinctes, sans magie :

```php
$page = $pennylane->customerInvoices()->list(limit: 50);
$page->items;       // CustomerInvoiceDto[]
$page->hasMore;     // bool
$page->nextCursor;  // ?string

foreach ($pennylane->customerInvoices()->paginate($filters) as $invoice) { … }
```

`list()` rend une page et laisse le contrôle du curseur à l'appelant.
`paginate()` rend un `Generator` qui traverse tout, via le `CursorPaginator` du
plugin `saloonphp/pagination-plugin` — dont les quatre méthodes abstraites
(`getNextCursor`, `isLastPage`, `getPageItems`, `applyPagination`) mappent
exactement sur `{items, has_more, next_cursor}`.

`applyPagination()` re-sérialise `filter` et `sort` à chaque page. C'est le
piège documenté par Pennylane ; il a un test dédié.

La méthode `collect()` du plugin n'est **pas** utilisée : c'est le seul point
qui suggère `illuminate/collections`.

### 5.2 Filtres

```php
$pennylane->customerInvoices()->list(
    filters: FilterSet::make()
        ->gteq('date', '2026-01-01')
        ->in('customer_id', [12, 34])
        ->startWith('label', 'ACME'),
    sort: Sort::desc('date'),
);
```

`FilterOperator` est une enum backed sur les neuf opérateurs : une faute de
frappe devient une erreur de compilation plutôt qu'un 422 en production.

### 5.3 Exceptions

Le trait `HandlesPennylaneErrors` mappe le corps `{error, message, details}` :

| Code | Exception | Accesseurs |
|---|---|---|
| 401 | `UnauthorizedException` | |
| 403 | `ForbiddenException` | `missingScope()` |
| 404 | `NotFoundException` | |
| 409 | `ConflictException` | `existingId()` |
| 422 | `ValidationException` | `errorCode()`, `details()` |
| 429 | `RateLimitException` | `retryAfter()` |
| 5xx | `ServerException` | |

Toutes héritent de `PennylaneException`, qui expose `error()`, `message()`,
`details()` et la `Response` d'origine.

### 5.4 Rate limit et retry

`HasRateLimits` du plugin `saloonphp/rate-limit-plugin`, avec
`Limit::allow(25)->everySeconds(5)`.

Store : `MemoryStore` par défaut, n'importe quel PSR-16 injectable via le
`PsrStore` fourni par le plugin. La limite étant **par token**, un store
partagé est nécessaire dès que plusieurs process utilisent le même jeton — sous
Symfony, `new PsrStore(new Psr16Cache($cachePool))`.

Retry sur 429 et 5xx, intervalle lu dans `retry-after` plutôt qu'un backoff
aveugle.

### 5.5 Webhooks

`SignatureVerifier` est indépendant de Saloon. Il parse
`X-Pennylane-Signature` (`t=…,v1=…`), recalcule
`hmac_sha256("{t}.{corps_brut}", $secret)`, compare en `hash_equals`, et rejette
au-delà d'une tolérance configurable (5 minutes par défaut).

Sa signature impose le **corps brut** en `string`, pas un tableau : re-sérialiser
le JSON invaliderait silencieusement la vérification.

## 6. Contrainte d'agnosticité

Le SDK ne doit dépendre d'aucun framework.

**Vérifié** : `saloonphp/saloon` v4 ne requiert que `guzzlehttp/guzzle` et
`psr/http-*`. `saloonphp/rate-limit-plugin` ne requiert que `saloon`.
`saloonphp/pagination-plugin` non plus — il *suggère* `illuminate/collections`
pour la seule méthode `collect()`, qu'on n'utilise pas.

**Conséquences sur l'outillage** :

- **PHP-CS-Fixer, pas Laravel Pint** — Pint tire `illuminate/*` en dépendance de
  développement. `friendsofphp/php-cs-fixer` avec le ruleset `@Symfony` rend le
  même service.
- **Store de rate limit en PSR-16**, jamais `LaravelCacheStore`.
- **Un job CI qui échoue** si `composer.lock` contient un paquet `illuminate/*`
  ou `laravel/*`. La contrainte devient vérifiable au lieu d'être une intention.

**Consommation depuis Symfony** : aucun état statique, aucune façade, aucune
configuration globale. Le connecteur se construit par injection.

```yaml
# services.yaml
Noneo\Sdk\Pennylane\Pennylane:
    arguments:
        $authenticator: '@app.pennylane.authenticator'
```

**Réserve assumée** : Saloon requiert Guzzle en dur. Le `vendor/` d'un projet
Symfony aura donc Guzzle en plus de `symfony/http-client`. Ça ne conflicte pas —
c'est un second client HTTP sur le disque. Saloon expose un contrat `Sender`
surchargeable, donc router les appels vers le HttpClient Symfony via un pont
PSR-18 reste possible plus tard, mais Guzzle restera installé.

Un éventuel bundle Symfony (configuration, autowiring, DSN) serait un paquet
séparé — `noneo/pennylane-bundle` — pour que ce SDK ne dépende jamais d'un
framework. Hors périmètre.

## 7. Découpage en lots

7 milestones, 20 tickets. Chaque ticket est un lot livrable et mergeable seul.

### v0.1 — Socle

1. Bootstrap du paquet et de la CI
2. Spec OpenAPI locale et outil de couverture
3. Connecteurs et authentification (Company / Firm / OAuth 2.0)
4. Pagination curseur, filtres et tri
5. Exceptions métier, rate limit et retry

Un **spike** est adossé au ticket 3 : valider que l'abstraction encaisse le
préfixe `/companies/{id}` du Firm **avant** de construire dessus. C'est une case
à cocher du ticket 3, pas un ticket de plus ; si elle échoue, on rouvre le
ticket 3 plutôt que de découvrir le problème au ticket 20.

### v0.2 — Facturation client

6. Categories et category groups
7. Customers (company / individual, contacts)
8. Customer Invoices
9. Credit notes, e-invoicing, envoi par email
10. Products et Quotes

Categories passe en premier : tous les endpoints `categorize` du reste du SDK
en dépendent.

### v0.3 — Achats

11. Suppliers
12. Supplier Invoices, purchase requests et orders

### v0.4 — Comptabilité

13. Journals, Ledger Accounts, Fiscal Years
14. Ledger Entries et Lines, lettrage, catégories analytiques
15. Trial balance et exports FEC / GL / AGL

### v0.5 — Banque

16. Bank accounts et Transactions, matching, catégorisation
17. Mandats SEPA, Pro Account et GoCardless

### v0.6 — Transverse

18. Commercial documents, file attachments, user profile, PA registrations
19. Webhooks et changes events

### v0.7 — Firm API

20. Connecteur Firm, `CompanyScope`, companies et ressources Firm

## 8. Définition de « fini »

Identique pour tous les tickets métier :

- tous les endpoints du groupe cochés dans la checklist du ticket ;
- un `Dto` par schéma de réponse, avec `fromArray()` testé sur un payload copié
  de la documentation ;
- un test `MockClient` par endpoint vérifiant **la méthode HTTP, l'URL, la query
  et le corps** — pas seulement l'absence d'exception ;
- `bin/coverage.php` reflète la progression ;
- PHPStan niveau max et PHP-CS-Fixer passent en CI.

## 9. Hors périmètre (YAGNI)

- Service provider Laravel, bundle Symfony
- Cache de réponses HTTP
- Retry métier au-delà de 429 et 5xx
- Conversion de devises, arithmétique sur les montants
- Génération de code automatique depuis la spec : la spec sert de référence de
  lecture et de contrôle de couverture, le code reste écrit à la main
