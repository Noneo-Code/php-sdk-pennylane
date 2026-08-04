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
    establishmentNo: Clear::value(), // "establishment_no": null — le champ est vidé
    iban: null,                      // absent du corps — l'IBAN est conservé
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

foreach ($firm->companies()->paginate() as $company) {
    $firm->company($company->id)->ledgerEntries()->list(limit: 10);
}
```

Le scope hérite du rate limit, des timeouts et de la politique de rejeu de son
`PennylaneFirm`. Il expose aussi le DMS du cabinet, absent de la Company API :

```php
$firm->company(1234)->dms()->uploadFile($handle, name: 'bilan-2026.pdf');
```

## Upload de fichiers

`fileAttachments()->upload()` accepte indifféremment une **ressource stream** ou
une chaîne. Préférez le stream : un PDF de plusieurs mégaoctets ne transite
alors jamais entier par la mémoire du process.

```php
$handle = fopen('/chemin/facture.pdf', 'r');

$attachment = $pennylane->fileAttachments()->upload($handle, filename: 'facture.pdf');

fclose($handle);

$attachment->id;  // à passer à customerInvoices()->import(...)
$attachment->url; // lien de téléchargement signé
```

L'usage principal est l'import d'une facture déjà émise ailleurs : on téléverse
le PDF, puis on rattache l'identifiant obtenu au document.

```php
$pennylane->customerInvoices()->import(
    fileAttachmentId: $attachment->id,
    date: '2026-01-31',
    deadline: '2026-03-01',
    customerId: 42,
    currencyAmountBeforeTax: '1000.00',
    currencyAmount: '1200.00',
    currencyTax: '200.00',
    invoiceLines: [],
);
```

Côté cabinet, le DMS a sa propre arborescence de dossiers :

```php
$folder = $firm->company(1234)->dms()->createFolder('Exercice 2026');
$firm->company(1234)->dms()->uploadFile($handle, name: 'grand-livre.pdf', parentFolderId: $folder->id);
```

## E-invoicing

La réforme de la facturation électronique passe par une plateforme agréée (PA).
Le SDK couvre les deux sens de circulation.

**Émission.** Une facture électronique s'importe depuis un fichier Factur-X, UBL
ou CII ; `sendToPa` décide si elle part dans la foulée ou attend un ordre
explicite.

```php
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;

$import = $pennylane->customerInvoices()->importEInvoice(
    fileContents: $handle,
    filename: 'facture.pdf',
    sendToPa: false,
    invoiceOptions: new EInvoiceImportOptions(customerId: 42),
);

$pennylane->customerInvoices()->sendToPa($import->id);
```

Le statut vit ensuite sur la facture, dans `eInvoicingStatus` — un
`EInvoicingStatus` : `submitted`, `sent`, `accepted`, `rejected`, `in_dispute`,
`collected`… `eInvoicingReason` porte le motif d'un refus.

**Réception.** Les factures fournisseur arrivent en `waiting_for_validation`
(`SupplierEInvoicingStatus`) et attendent une décision, chacune avec son
énumération de motifs :

```php
$pennylane->supplierInvoices()->approveEInvoice(42);
$pennylane->supplierInvoices()->disputeEInvoice(42, SupplierEInvoiceDisputeReason::WrongAmount);
$pennylane->supplierInvoices()->refuseEInvoice(42, SupplierEInvoiceRefusalReason::Other);
```

**Immatriculation.** Rien ne circule tant que l'entreprise n'est pas enregistrée
auprès de la plateforme. `paRegistrations()` en donne l'état, par SIREN et par
sens d'échange :

```php
foreach ($pennylane->paRegistrations()->list()->items as $registration) {
    $registration->status;            // PaRegistrationStatus::Activated, Pending…
    $registration->exchangeDirection; // Emission, Reception, EmissionAndReception
}
```

La transmission étant asynchrone, ne sondez pas l'API : abonnez-vous à
l'événement `customer_invoice.e_invoicing_status_updated` (section suivante).

## Webhooks

Un abonnement se crée par l'API. **Le secret n'est rendu qu'une fois**, à la
création : `create()` rend un `CreatedWebhookSubscriptionDto`, seul DTO à porter
le champ `secret`. Un `get()` ultérieur ne le redonnera pas.

```php
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;

$subscription = $pennylane->webhookSubscriptions()->create(
    callbackUrl: 'https://exemple.test/webhooks/pennylane',
    events: [WebhookEventType::CustomerInvoiceEInvoicingStatusUpdated],
);

$subscription->secret; // à ranger dans votre coffre à secrets, maintenant
```

Côté réception, `Webhook\SignatureVerifier` valide l'en-tête
`X-Pennylane-Signature` (format `t=<unix>,v1=<hex>`) : HMAC SHA-256 sur
`{timestamp}.{corps}`, comparaison en temps constant, et fenêtre anti-rejeu de
**300 s** par défaut. Il travaille sur le **corps brut** : re-sérialiser un
tableau décodé décalerait une espace, et l'empreinte ne correspondrait plus.

```php
use Noneo\Sdk\Pennylane\Webhook\SignatureVerifier;
use Noneo\Sdk\Pennylane\Webhook\WebhookEvent;

final class PennylaneWebhookController
{
    public function __construct(private readonly SignatureVerifier $verifier)
    {
    }

    public function __invoke(Request $request): Response
    {
        $rawBody = $request->getContent();

        if (!$this->verifier->verify($rawBody, $request->headers->get('X-Pennylane-Signature', ''))) {
            return new Response('Signature invalide.', 401);
        }

        $event = WebhookEvent::fromArray(json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR));

        // Répondez vite : Pennylane rejoue l'événement en cas d'échec ou de
        // lenteur, et $event->retryCount dit combien de fois il l'a déjà tenté.
        $this->bus->dispatch(new HandlePennylaneEvent($event->event, $event->object));

        return new Response('', 200);
    }
}
```

`WebhookEvent` expose `event`, `object`, `previousAttributes` (l'état antérieur
des seuls champs modifiés), `companyId`, `firmId` et `retryCount`. Le
vérificateur est indépendant de Saloon : il s'instancie avec le seul secret.

```php
$verifier = new SignatureVerifier($secret, toleranceSeconds: 900);
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

## Exemples

Le dossier `examples/` contient des scripts exécutables, autonomes, qui lisent
leur jeton dans l'environnement. Ils ne sont pas livrés dans l'archive Composer.

| Script | Ce qu'il montre |
|---|---|
| `quickstart.php` | Identité du jeton, liste filtrée et triée, pagination complète |
| `invoicing_lifecycle.php` | Client → brouillon → finalisation → envoi par e-mail → marquage payé |
| `firm_portfolio.php` | Portefeuille cabinet, scope entreprise, lecture d'écritures |
| `webhook_receiver.php` | Vérification de signature et parsing d'événement, sans framework |
| `einvoice_import.php` | Import d'une facture électronique et suivi de son statut |

```bash
PENNYLANE_TOKEN=… php examples/quickstart.php
PENNYLANE_FIRM_TOKEN=… php examples/firm_portfolio.php
```

Les scripts d'écriture (`invoicing_lifecycle.php`, `einvoice_import.php`) créent
de vraies données : réservez-les à un environnement de test.

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
- `php bin/fetch-openapi.php` — régénère les specs depuis les URLs officielles
- `php bin/spec-watch.php` — signale une dérive entre specs committées et publiées

### Specs OpenAPI

Les deux specs committées à la racine sont téléchargées telles quelles depuis
Pennylane, puis réindentées pour rester lisibles et diffables :

| Fichier | Source |
|---|---|
| `pennylane-api-v2.json` | <https://pennylane.readme.io/openapi/accounting.json> |
| `pennylane-api-firm-v1.json` | <https://firm-pennylane.readme.io/openapi/referentials.json> |

`bin/spec-watch.php` compare les opérations committées à celles publiées —
`operationId`, méthode et chemin, jamais le texte du fichier, qui bouge à chaque
republication. En CI, le job `spec-watch` l'exécute et ouvre une issue étiquetée
`spec-watch` dès qu'une dérive apparaît, sans doublonner une issue déjà ouverte.

Deux gestes restent manuels, hors dépôt :

1. créer le schedule dans **CI/CD > Schedules** (hebdomadaire, lundi matin) ;
2. exposer un token projet en variable CI `SPEC_WATCH_TOKEN`, portée `api` —
   `$CI_JOB_TOKEN` ne permet pas de créer une issue. Sans ce token, le job
   affiche le rapport sans rien ouvrir.

Pour rejouer la veille hors schedule, lancez un pipeline avec la variable
`SPEC_WATCH_FORCE=1`.

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

### Politique de nouvelle tentative

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
$pennylane = new Noneo\Sdk\Pennylane\Pennylane($authenticator, maxRetries: 5);

// Aucun rejeu : à l'appelant de décider quoi faire de l'échec.
$firm = new Noneo\Sdk\Pennylane\PennylaneFirm($authenticator, maxRetries: 0);
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
$pennylane = new Noneo\Sdk\Pennylane\Pennylane($authenticator, requestTimeout: 300);

$firm = new Noneo\Sdk\Pennylane\PennylaneFirm(
    $authenticator,
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

## Dépendances et Guzzle

Le SDK repose sur [Saloon](https://docs.saloon.dev/) v4, qui requiert
`guzzlehttp/guzzle` en dur. Un projet Symfony consommant ce SDK aura donc
Guzzle installé en plus de `symfony/http-client` — les deux coexistent sans
conflit, ce sont simplement deux clients HTTP présents sur le disque.

## Licence

MIT
