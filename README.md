# noneo/sdk-pennylane

SDK PHP pour l'API [Pennylane](https://pennylane.readme.io) — Company API v2
et Firm API v1.

Le paquet est **agnostique de tout framework** : il ne dépend ni de Symfony,
ni de Laravel, et s'utilise par simple injection de dépendances.

## Installation

```bash
composer require noneo/sdk-pennylane
```

Requiert PHP `>=8.3` — la contrainte vient des constantes de classe typées
(PHP 8.3) ; rien dans le SDK n'exige 8.4.

## Démarrage

Le client s'instancie avec le seul jeton d'API. Les méthodes de ressource
rendent des DTO, jamais une réponse brute.

```php
use Noneo\Sdk\Pennylane\Pennylane;

$pennylane = new Pennylane($companyToken);

$invoice = $pennylane->customerInvoices()->get(42);
echo $invoice->amount;
```

Sous Symfony, le client se déclare comme n'importe quel service :

```yaml
# services.yaml
Noneo\Sdk\Pennylane\Pennylane:
    arguments:
        $apiToken: '%env(PENNYLANE_TOKEN)%'
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

## Montants : jamais de `float`

Pennylane attend ses montants en **chaînes décimales** (`"120.50"`), et le SDK
refuse tout `float`, dans les corps de requête comme dans les query et les
filtres :

```php
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;

new NewProductInvoiceLine(
    productId: 42,
    quantity: '2.5',              // une quantité fractionnaire est une chaîne
    rawCurrencyUnitPrice: '120.50',
);

// InvalidArgumentException : « Le champ « raw_currency_unit_price » a reçu le
// float 120.5. Pennylane attend les montants en chaîne décimale… »
new NewProductInvoiceLine(productId: 42, quantity: 1, rawCurrencyUnitPrice: 120.50);
```

Un `float` PHP ne représente pas exactement un décimal : `json_encode(120.50)`
écrit `120.5`, et `json_encode(0.1 + 0.2)` écrit `0.30000000000000004`. Une
écriture comptable fausse d'un centime est le pire défaut possible pour ce SDK
— silencieuse, et comptablement fausse. Mieux vaut une exception à l'appel
qu'un grand livre à corriger.

La règle **n'a aucune exception** : `2.0` est un `float` comme un autre, y
compris pour une quantité, que l'API type pourtant en `number`. Une règle sans
exception est la seule qui s'explique, et la seule qui ne laisse pas de trou.
Les entiers (`quantity: 3`) restent bien sûr acceptés.

Le contrôle s'applique à toute profondeur — un montant niché dans
`invoice_lines[0]` est trouvé — et le message cite le chemin complet du champ
fautif.

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

Les factures d'une entreprise se lisent depuis le scope. Ces deux ressources
sont **en lecture seule** : la Firm API n'expose que la liste et le détail, là
où la Company API accepte aussi la création, l'import ou la validation
comptable.

```php
$firm->company(1234)->supplierInvoices()->list(limit: 50);
$firm->company(1234)->customerInvoices()->get(4242);
```

Les changelogs du scope suivent les mêmes groupes, factures comprises :

```php
$firm->company(1234)->changelogs()->supplierInvoices(startDate: '2026-01-01T00:00:00Z');

foreach ($firm->company(1234)->changelogs()->paginateCustomerInvoices() as $change) {
    $change->id;
}
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

Pennylane garde un import générique, `eInvoices()->import()`, où le sens est
porté par un `EInvoiceImportType` plutôt que par la ressource appelée. Il est
**déprécié** et n'accepte aucune option d'import : préférez les deux méthodes
spécialisées ci-dessus.

```php
use Noneo\Sdk\Pennylane\Dto\EInvoice\EInvoiceImportType;

$import = $pennylane->eInvoices()->import(
    fileContents: $handle,
    type: EInvoiceImportType::Supplier,
    filename: 'facture.pdf',
);
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

Côté réception, `Webhook\SignatureVerifier` valide l'en-tête dont le nom est
figé dans `SignatureVerifier::HEADER` (`X-Pennylane-Signature`). Passez-lui le
**corps brut** : re-sérialiser un tableau décodé décalerait une espace, et
l'empreinte ne correspondrait plus.

`constructEvent()` vérifie **puis** décode. Préférez-la à `verify()` suivi de
`WebhookEvent::fromArray()` : elle rend impossible de traiter un événement dont
la signature n'a pas été contrôlée.

```php
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Exception\InvalidWebhookSignatureException;
use Noneo\Sdk\Pennylane\Webhook\SignatureVerifier;

final class PennylaneWebhookController
{
    public function __construct(private readonly SignatureVerifier $verifier)
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $event = $this->verifier->constructEvent(
                $request->getContent(),
                $request->headers->get(SignatureVerifier::HEADER, ''),
            );
        } catch (InvalidWebhookSignatureException) {
            return new Response('Signature invalide.', 401);
        }

        // Répondez vite : Pennylane rejoue l'événement en cas d'échec ou de
        // lenteur, et $event->retryCount dit combien de fois il l'a déjà tenté.
        $this->bus->dispatch(new HandlePennylaneEvent($event->eventName, $event->object));

        return new Response('', 200);
    }
}
```

`WebhookEvent` expose `event` (un `WebhookEventType`, ou `null` si Pennylane a
publié un type postérieur à cette version du SDK), `eventName` (la valeur brute,
toujours renseignée), `object`, `previousAttributes` (l'état antérieur des seuls
champs modifiés), `companyId`, `firmId` et `retryCount`. Le vérificateur est
indépendant de Saloon : il s'instancie avec le seul secret.

### Deux schémas de signature, et pourquoi

Pennylane ne documente pas publiquement le format de sa signature — la
fonctionnalité est en bêta, et les intégrations existantes ont fait des paris
**incompatibles entre eux**. Le SDK porte donc les deux et refuse de choisir à
la place de l'API :

| `WebhookSignatureScheme` | En-tête | HMAC-SHA256 sur | Anti-rejeu |
| --- | --- | --- | --- |
| `Timestamped` *(défaut)* | `t=<unix>,v1=<hex>` | `{timestamp}.{corps}` | fenêtre de 300 s |
| `BodyOnly` | signature seule, hex (casse indifférente) ou base64, préfixe `sha256=`/`hmac-sha256=` toléré | le corps brut | **aucun** |

```php
use Noneo\Sdk\Pennylane\Webhook\WebhookSignatureScheme;

// Défaut : schéma horodaté, fenêtre anti-rejeu élargie à 900 s.
$verifier = new SignatureVerifier($secret, toleranceSeconds: 900);

// Mode compatibilité, à n'activer qu'après avoir constaté ce format :
$verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);
```

Le schéma se choisit **à la construction, jamais d'après l'en-tête reçu** : une
auto-détection laisserait un attaquant réclamer le format sans horodatage, donc
sans anti-rejeu. Sous `BodyOnly`, la protection contre le rejeu revient à
l'application — dédupliquez sur `WebhookEvent::$id` (`$retryCount` distingue une
nouvelle tentative de Pennylane d'un rejeu hostile).

Comparaison en temps constant (`hash_equals`) dans les deux cas.

## OAuth 2.0

`Auth\OAuthClient` ne fait que le ballet OAuth et rend un
`Auth\AccessToken` — un DTO du SDK (`token`, `refreshToken`, `expiresAt`) à
persister tel quel côté application :

```php
use Noneo\Sdk\Pennylane\Auth\OAuthClient;

$oauth = new OAuthClient($clientId, $clientSecret, $redirectUri);

// 1. Rediriger l'utilisateur
$url = $oauth->getAuthorizationUrl(scopes: ['customer_invoices:all'], state: $state);

// 2. Échanger le code au retour
$accessToken = $oauth->getAccessToken($code);

// 3. Rafraîchir si besoin, puis consommer : un jeton OAuth est un Bearer
//    comme un autre.
$accessToken = $oauth->refreshIfExpired($accessToken);
$pennylane = new Pennylane($accessToken->token);
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

## Tester votre intégration

`Testing\MockPennylane` simule l'API dans vos tests, sans réseau et sans
qu'aucun symbole Saloon n'entre dans votre code. Les réponses s'associent par
méthode HTTP et chemin — celui de la documentation Pennylane, wildcards `*`
admis — ou se servent en file séquentielle (liste sans clés) :

```php
use Noneo\Sdk\Pennylane\Testing\MockPennylane;

$mock = MockPennylane::create([
    'GET /customer_invoices/42' => MockPennylane::json(['id' => 42, /* … */]),
    'POST /customers'           => MockPennylane::json(['id' => 7, /* … */], 201),
    'GET /suppliers/*'          => MockPennylane::error(404),
]);

$pennylane = $mock->pennylane();   // ou $mock->firm() côté cabinet
$monService->facturer($pennylane); // le code sous test, inchangé

$mock->assertSent('POST', '/customers');
$mock->assertNothingElseSent();
```

Côté Firm, les chemins gardent le scope d'entreprise :
`'GET /companies/12/ledger_entries'`. Une requête qu'aucune entrée ne couvre
lève `UnexpectedRequestException` — le test décrit tout ce qui part, ou il
échoue. Les assertions (`assertSent`, `assertNotSent`, `assertNothingSent`,
`assertNothingElseSent`) s'appuient sur PHPUnit, présent dans les
`require-dev` de tout projet qui teste.

Les réponses d'erreur (`MockPennylane::error(404)`) lèvent les mêmes
exceptions que l'API réelle (`NotFoundException`, etc.) : le chemin d'échec se
teste comme le chemin nominal.

## Développement

```bash
composer install
composer check   # test + stan + cs + schema, comme en CI
```

Scripts disponibles :

- `composer test` — suite unitaire : ni réseau, ni jeton
- `composer test:integration` — suite contre l'API réelle (voir plus bas)
- `composer stan` — PHPStan niveau `max` (`bin`, `src`, `tests`)
- `composer cs` — vérification PHP-CS-Fixer (`@Symfony`, mode `--dry-run`)
- `composer schema` — fixtures et enums confrontés aux schémas OpenAPI
- `composer check` — enchaîne les quatre
- `php bin/coverage.php` — couverture des endpoints face à la spec OpenAPI
- `php bin/fetch-openapi.php` — régénère les specs depuis les URLs officielles
- `php bin/spec-watch.php` — signale une dérive entre specs committées et publiées

### Tests d'intégration

La suite par défaut est intégralement unitaire : les payloads y sont des
copies figées de la documentation. Elle ne peut donc pas dire si l'API se
comporte encore comme le SDK le suppose. C'est le rôle de `tests/Integration/`,
exclue de `composer test` et lancée à la demande :

```bash
PENNYLANE_API_TOKEN=… composer test:integration
```

Sans `PENNYLANE_API_TOKEN`, chaque test s'ignore de lui-même — la commande
reste verte, elle ne vérifie simplement rien. Le connecteur est partagé par
toute la suite, pour qu'un seul compteur de débit protège le quota 25/5 s.

Les tests d'écriture demandent un **second verrou** :

```bash
PENNYLANE_API_TOKEN=… PENNYLANE_SANDBOX=1 composer test:integration
```

**Utilisez un compte de bac à sable, jamais une comptabilité de production.**
L'API ne permet pas de supprimer ce que ces tests créent — une facture
finalisée, une écriture comptable et une pièce jointe restent en place. Chaque
objet créé porte un tag de run (`sdk-test-…`) dans un champ libre : c'est par
lui qu'on les retrouve, et le ménage reste manuel, par conception.

En CI, le job `integration` est `when: manual` et n'apparaît jamais dans un
pipeline de MR. Le jeton se définit dans **CI/CD > Variables**, masqué et
protégé.

### Conformité des fixtures aux schémas

`bin/coverage.php` valide les *chemins* : chaque endpoint documenté a bien une
requête. `bin/schema-check.php` valide les *formes* : si Pennylane renomme ou
retype un champ, les tests unitaires continueraient de valider l'ancienne
forme sans que rien ne le signale.

Le lien entre une fixture et le schéma dont elle se réclame est explicite —
les `operationId` de Pennylane ne suivant aucune convention dérivable :

```php
#[DocumentedBy('getCustomerInvoiceInvoiceLines', pointer: 'items')]
public static function documentedPayload(): array { … }

#[DocumentedBy('getCustomer', pointer: 'payment_conditions')]
enum PaymentConditions: string { … }
```

Le `pointer` descend de propriété en propriété et traverse les tableaux
d'eux-mêmes ; un segment `champ=valeur` désigne une branche précise d'un
`oneOf` discriminé (`status=refused.reason`). Un enum est confronté aux valeurs
de la spec **dans les deux sens** : une valeur de la spec absente du SDK ferait
lever un `from()` sur une réponse pourtant valide.

Une fixture nommée `documented…()` sans annotation est signalée comme non
rattachée : impossible d'en ajouter une qui échappe au contrôle en silence. Un
écart assumé — l'enveloppe de webhook, que la spec ne décrit pas — se documente
dans la liste d'exclusions en tête de `bin/schema-check.php`.

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

Une requête qui existe n'est pas pour autant appelable : le scope
`/companies/{id}` est porté par `CompanyScope`, pas par la requête, donc une
même classe sert les deux connecteurs et peut n'être exposée que d'un côté.
`ResourceGraph` remonte donc, de ressource en ressource, ce que chaque
connecteur atteint réellement, et l'audit distingue trois portées — `Pennylane`,
`PennylaneFirm`, `CompanyScope`. Une opération couverte par une requête
qu'aucune ressource n'expose est signalée « injoignable » et fait sortir
`bin/coverage.php` en 3.

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

## Sécurité

### Transport

Trois invariants s'appliquent à tous les connecteurs (`Pennylane`,
`PennylaneFirm`, `CompanyScope`, `OAuthClient`) :

- **HTTPS obligatoire.** Une requête authentifiée hors HTTPS lève
  `InsecureBaseUrlException` **avant** de partir : le jeton ne quitte jamais le
  process. Les hôtes de bouclage (`localhost`, `127.0.0.1`, `[::1]`) font
  exception, pour permettre un serveur d'enregistrement en tests d'intégration.
- **Redirections non suivies.** `allow_redirects` est désactivé et un 3xx lève
  `RedirectException`. Une API JSON ne redirige pas ; suivre un 301 rejouerait
  l'en-tête `Authorization` vers l'hôte annoncé par la réponse — un tiers,
  potentiellement.
- **`User-Agent` identifiant**, de la forme `noneo-sdk-pennylane/0.1.0`, pour
  que le support Pennylane retrouve nos appels dans leurs journaux. La version
  vit dans `Pennylane::VERSION` et suit le CHANGELOG.

### Secrets dans les traces

Tout paramètre portant un secret — jeton d'API, `clientSecret`, secret de
webhook, `AccessToken` OAuth — est marqué `#[\SensitiveParameter]`. PHP remplace
alors sa valeur par `Object(SensitiveParameterValue)` dans les arguments de
trace. Un test relit `src/` à chaque exécution et refuse tout nouveau paramètre
au nom évocateur qui oublierait l'attribut.

**Réglage d'exploitation à vérifier :** activez

```ini
zend.exception_ignore_args = 1
```

C'est la valeur de `php.ini-production`, mais **pas** le défaut de PHP. Sans
elle, PHP conserve les arguments de chaque frame, et les frames internes de
Saloon reçoivent la `Response` — dont la requête PSR signée, `readonly` et hors
de portée du SDK. Autrement dit : le SDK garantit que ni l'exception ni ses
propres frames ne portent le jeton, mais seul ce réglage neutralise celles de
ses dépendances. La trace *textuelle* (`getTraceAsString()`, celle qui part
dans les logs) ne rend jamais que les types des arguments : elle est sûre dans
les deux cas.

## Dépendances et Guzzle

Le SDK repose sur [Saloon](https://docs.saloon.dev/) v4, mais en fait un
**détail d'implémentation** : aucune classe publique n'expose de type
`Saloon\*` — ni en signature, ni par héritage — et un test par réflexion
(`PublicApiSurfaceTest`) le verrouille à chaque exécution. Une montée de
version de Saloon, voire son remplacement, ne peut donc pas casser un projet
consommateur. La migration vers un transport PSR-18 pur a été étudiée et
écartée : réécrire les ~180 requêtes, la pagination et le rate-limit pour un
bénéfice hypothétique ne se justifierait que si Saloon devenait un problème
réel (abandon du projet, incompatibilité PHP).

Saloon requiert `guzzlehttp/guzzle` en dur. Un projet Symfony consommant ce
SDK aura donc Guzzle installé en plus de `symfony/http-client` — les deux
coexistent sans conflit, ce sont simplement deux clients HTTP présents sur le
disque.

## Licence

MIT
