# Erreurs, rate limit et timeouts

## Hiérarchie d'exceptions

Les erreurs Pennylane sont exposées via une hiérarchie d'exceptions dédiée,
jamais la `RequestException` générique de Saloon :

| Statut | Exception | Accesseurs |
|---|---|---|
| 3xx | `RedirectException` | `location()` |
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
`errorCode()`, `details()`, `status()`, `responseHeaders()` et
`responseBody()`. Un `catch` sur la classe mère couvre donc toute la surface
d'erreur, y compris une page HTML renvoyée par un proxy en amont sur un 502 :
le corps illisible n'efface ni le statut ni le corps brut.

L'exception ne retient **aucun** objet Saloon. C'est délibéré : depuis une
`Response` on remonte au `PendingRequest`, donc à ses en-têtes, donc au
`Authorization: Bearer …`. Un `Sentry::captureException()` suffirait alors à
journaliser le jeton. Seules des copies sont conservées :

- `responseBody()` — corps brut **tronqué à 2 000 octets**, sur une frontière
  de caractère UTF-8 ; un 502 HTML de proxy pèse parfois des centaines de Ko ;
- `responseHeaders()` — en-têtes retenus sur **allowlist** (`Retry-After`,
  `Content-Type`, `Date`, `Location`, `X-Request-Id`, `X-Correlation-Id` et les
  familles `RateLimit-*`). Un en-tête hors liste — `Set-Cookie` en tête — est
  écarté plutôt que deviné ;
- `error()` et `getMessage()` — tronqués à 500 caractères.

```php
try {
    $pennylane->customerInvoices()->get(42);
} catch (Noneo\Sdk\Pennylane\Exception\NotFoundException $e) {
    // $e->error(), $e->status(), $e->responseBody(), $e->responseHeaders()
} catch (Noneo\Sdk\Pennylane\Exception\PennylaneException $e) {
    // tout le reste
}
```

## Rate limit

Pennylane limite à 25 requêtes par tranche de 5 secondes, **par token** — donc
partagé entre tous les process qui l'utilisent. Le SDK segmente son compteur
par jeton (haché), afin que deux entreprises partageant un même store ne se
disputent pas un unique quota.

Par défaut, le SDK suit ce quota en mémoire, ce qui ne suffit plus dès qu'un
jeton est partagé entre plusieurs process. Injectez alors n'importe quel cache
PSR-16 (`Psr\SimpleCache\CacheInterface`) partagé — le SDK construit son store
interne autour :

```php
use Symfony\Component\Cache\Psr16Cache;

$pennylane = new Noneo\Sdk\Pennylane\Pennylane(
    $companyToken,
    rateLimitCache: new Psr16Cache($cachePool),
);
```

## Politique de nouvelle tentative

L'API Pennylane n'expose aucune clé d'idempotence : rien ne permet de rejouer
un `POST` sans risquer un doublon. La politique du SDK en découle.

| Statut | Méthodes rejouées | Motif |
| --- | --- | --- |
| 429 | toutes | La requête n'a pas été traitée : la rejouer est sans effet de bord. |
| 5xx | `GET`, `HEAD`, `PUT`, `DELETE` | Un 502 de proxy ne dit pas si Pennylane a traité l'appel — seules les méthodes idempotentes supportent le doute. |
| 5xx | jamais `POST` ni `PATCH` | Rejouer un `POST /customer_invoices` créerait une seconde facture. |
| 4xx (hors 429) | aucune | Une requête invalide le restera. |

Le délai d'attente suit le serveur quand il se prononce, et un backoff sinon :

- `Retry-After` présent et exploitable → honoré tel quel, plafonné à **60 s** ;
- sinon → backoff exponentiel de base **0,5 s**, doublé à chaque tentative,
  plafonné à **8 s**, avec un jitter soustractif de 25 % au plus qui
  désynchronise les rafales de plusieurs process.

Le nombre de **nouvelles** tentatives après l'échec initial vaut `3` par défaut
(soit quatre appels HTTP au pire) et se règle au constructeur des deux
connecteurs ; `0` désactive tout rejeu. Un `CompanyScope` hérite du réglage de
son `PennylaneFirm`.

```php
$pennylane = new Noneo\Sdk\Pennylane\Pennylane($companyToken, maxRetries: 5);

// Aucun rejeu : à l'appelant de décider quoi faire de l'échec.
$firm = new Noneo\Sdk\Pennylane\PennylaneFirm($firmToken, maxRetries: 0);
$firm->company(42)->ledgerEntries()->list(); // hérite de maxRetries: 0
```

Une fois les tentatives épuisées, l'exception correspondant au statut est levée
(`RateLimitException` pour un 429, `ServerException` pour un 5xx).

## Timeouts

Sans borne explicite, un appel lent — les exports comptables au premier chef —
bloque le process indéfiniment. Le SDK applique donc deux limites à toutes ses
requêtes :

| Réglage | Défaut | Portée |
| --- | --- | --- |
| `connectTimeout` | **10 s** | Établissement de la connexion TCP/TLS. |
| `requestTimeout` | **60 s** | Échange complet, en-têtes et corps compris. |

Les deux se règlent au constructeur de `Pennylane`, `PennylaneFirm` et
`OAuthClient`, en secondes. Un `CompanyScope` hérite des valeurs de son
`PennylaneFirm`.

```php
// Un export volumineux mérite plus que la minute par défaut.
$pennylane = new Noneo\Sdk\Pennylane\Pennylane($companyToken, requestTimeout: 300);

$firm = new Noneo\Sdk\Pennylane\PennylaneFirm(
    $firmToken,
    requestTimeout: 120,
    connectTimeout: 5,
);
// hérite de 120 s / 5 s
$firm->company(42)->exports()->exportFec('2026-01-01', '2026-12-31');
```

`0` vaut « pas de limite » au sens de Guzzle — à réserver aux traitements hors
ligne, jamais à un process qui sert une requête HTTP.

Attention à la combinaison avec les nouvelles tentatives : le timeout borne
**chaque** appel, pas la série. Avec `maxRetries: 3`, quatre appels de 60 s plus
les délais d'attente peuvent occuper le process plusieurs minutes.
