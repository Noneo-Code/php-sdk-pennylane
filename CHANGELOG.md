# Changelog

Toutes les évolutions notables de ce paquet sont consignées ici.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le
versionnement suit [Semantic Versioning](https://semver.org/lang/fr/).

Chaque version publiée porte un tag Git `vX.Y.Z` correspondant à sa section.

## [Non publié]

### Sécurité

- `PennylaneException` ne retient plus aucun objet Saloon. La `Response`
  donnait accès au `PendingRequest` et donc à l'en-tête `Authorization` : un
  `Sentry::captureException()` pouvait journaliser le jeton.
- Corps d'erreur tronqué à 2 000 octets (sur une frontière de caractère UTF-8)
  et message à 500 caractères : un 502 HTML de proxy n'inonde plus les logs.
- En-têtes de réponse conservés sur allowlist ; `Set-Cookie` et tout en-tête
  inconnu sont écartés.
- `#[\SensitiveParameter]` sur tout paramètre portant un secret, avec un test
  qui relit `src/` pour refuser les oublis futurs.
- Redirections HTTP non suivies (`allow_redirects: false`) : suivre un 301
  rejouerait l'en-tête `Authorization` vers l'hôte annoncé par la réponse.
- Refus d'émettre une requête authentifiée hors HTTPS, hôtes de bouclage
  exceptés.

### Ajouté

- Suite d'intégration optionnelle (`tests/Integration/`, `composer
  test:integration`) contre l'API réelle : lecture, pagination sur deux pages,
  filtre `gteq` rejoué de page en page, contournement `start_date` des
  changelogs, en-têtes de quota, et cas d'erreur 404/401/422. Chaque test
  s'ignore sans `PENNYLANE_API_TOKEN` ; les écritures exigent en plus
  `PENNYLANE_SANDBOX=1`. Job CI `integration` manuel, hors pipelines de MR.
- `bin/schema-check.php` et `composer schema` : les fixtures des tests et les
  55 enums backed sont confrontés aux schémas OpenAPI committés. Le
  rattachement est explicite via l'attribut `#[DocumentedBy]`, avec pointeur de
  propriété et sélecteur de branche (`status=refused.reason`). Une fixture non
  annotée est signalée, un écart assumé se documente en exclusion. Bloquant en
  CI (job `schema-check`) et intégré à `composer check`.
- `Pennylane::VERSION` et un en-tête `User-Agent` de la forme
  `noneo-sdk-pennylane/x.y.z` sur toutes les requêtes.
- `RedirectException` (3xx, avec `location()`) et `InsecureBaseUrlException`.
- `PennylaneException::status()`, `responseHeaders()` et `responseBody()`.

### Changé (rupture)

- **Aucun `float` ne quitte plus le SDK.** Un `float` dans un corps de requête,
  une query ou un filtre lève une `\InvalidArgumentException` citant le chemin
  du champ fautif et la chaîne décimale à écrire à la place. Un `float` ne
  représente pas exactement un décimal : `json_encode(120.50)` écrit `120.5`,
  et une écriture comptable fausse d'un centime ne se voit pas. La règle n'a
  aucune exception, `2.0` compris.
- Les douze DTO de lignes (facture, devis, abonnement, demande d'achat)
  déclarent leur `quantity` en `int|string` au lieu de `int|float` : une
  quantité fractionnaire s'écrit `'2.5'`.
- `PennylaneException::response()` est **supprimé** — c'était le chemin d'accès
  au jeton. Remplacé par `status()`, `responseHeaders()` et `responseBody()`.
- Un 3xx lève désormais une exception au lieu de rendre une réponse vide.

## [0.1.0] - 2026-08-03

Première version : couverture de la Company API v2 et de la Firm API v1 de
Pennylane, sans dépendance à un framework.

### Ajouté

**Socle**

- Connecteurs `Pennylane` (Company API v2) et `PennylaneFirm` (Firm API v1),
  bâtis sur Saloon 4 et acceptant n'importe quel `Authenticator`.
- `CompanyScope`, obtenu par `PennylaneFirm::company($id)`, qui préfixe
  `/companies/{id}` à toutes les requêtes et hérite des réglages du cabinet.
- Client OAuth 2.0 (`Auth\OAuthClient`) : URL d'autorisation, échange du code,
  rafraîchissement du jeton.
- Pagination par curseur (`CursorPage`, `CursorPaginator`) et par page
  (`Page`) ; chaque ressource expose `list()` et un `paginate()` qui rend un
  `Generator` en repropageant filtres et tri à chaque page.
- Filtres typés (`FilterSet`, les neuf `FilterOperator`) et tri (`Sort`).
- `Http\Clear` : distinguer « champ absent du corps » de « champ vidé
  explicitement » dans les mises à jour partielles.
- Énumérations PHP backed pour toutes les énumérations de l'API — devises,
  taux de TVA, statuts, langues de facturation.

**Ressources Company API**

- Clients : `customers()` (company et individual), contacts, catégorisation.
- Facturation client : `customerInvoices()` (cycle de vie complet, lignes,
  sections, paiements, transactions rapprochées, annexes, avoirs, envoi par
  e-mail), `quotes()`, `products()`, `billingSubscriptions()`,
  `commercialDocuments()`.
- Achats : `suppliers()`, `supplierInvoices()`, `purchaseRequests()`.
- Comptabilité : `journals()`, `ledgerAccounts()`, `ledgerEntries()`,
  `ledgerEntryLines()`, `fiscalYears()`, `trialBalance()`, `categories()`,
  `categoryGroups()`.
- Exports : `exports()` — FEC, grand livre, grand livre analytique.
- Banque : `bankAccounts()`, `bankEstablishments()`, `transactions()` avec
  rapprochement et catégorisation.
- Encaissement : `sepaMandates()`, `proAccountMandates()`,
  `gocardlessMandates()`.
- E-invoicing : imports de factures électroniques client et fournisseur,
  `sendToPa()`, cycle d'approbation fournisseur, `paRegistrations()`.
- Pièces jointes : `fileAttachments()->upload()`, acceptant une ressource
  stream ou une chaîne.
- Webhooks : `webhookSubscriptions()` et `changelogs()`.
- `me()` : identité, entreprise et scopes du jeton.

**Ressources Firm API**

- `companies()` : portefeuille du cabinet, itérable et filtrable.
- Sous le scope d'une entreprise : journaux, plan comptable, écritures et
  lignes d'écriture, catégories, fournisseurs, clients, comptes bancaires,
  transactions, exercices fiscaux, balance générale, exports, changelogs,
  pièces jointes et DMS (`dms()`).

**Robustesse**

- Hiérarchie d'exceptions dédiée sous `PennylaneException`, une classe par
  statut, avec accès au code d'erreur, aux détails et à la réponse d'origine —
  y compris quand un proxy renvoie du HTML sur un 5xx.
- Respect du quota Pennylane (25 requêtes / 5 s), compteur segmenté par jeton
  haché, store PSR-16 injectable pour les déploiements multi-process.
- Nouvelles tentatives calibrées sur l'absence d'idempotence de l'API : jamais
  de rejeu d'un `POST` ou d'un `PATCH` sur 5xx, `Retry-After` honoré et
  plafonné, backoff exponentiel avec jitter sinon.
- Timeouts de connexion (10 s) et de requête (60 s) appliqués par défaut à
  toutes les requêtes, réglables au constructeur.

**Vérification des webhooks**

- `Webhook\SignatureVerifier` : HMAC SHA-256 et fenêtre anti-rejeu de 300 s.
- `Webhook\WebhookEvent::fromArray()` : décodage typé de l'enveloppe
  d'événement.

**Outillage**

- `bin/fetch-openapi.php` : récupération des specs depuis les URL officielles.
- `bin/coverage.php` : audit de couverture des endpoints, rapproché sur la
  méthode HTTP et le chemin plutôt que sur le nom de classe ; échoue en CI sur
  une régression.
- `bin/spec-watch.php` : veille hebdomadaire des specs publiées, avec ouverture
  automatique d'une issue en cas de dérive.

[Non publié]: https://gitlab.noneo.fr/noneo/php-packages/php-sdk-pennylane/-/compare/v0.1.0...main
[0.1.0]: https://gitlab.noneo.fr/noneo/php-packages/php-sdk-pennylane/-/tags/v0.1.0
