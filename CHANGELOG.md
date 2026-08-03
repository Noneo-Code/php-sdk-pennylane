# Changelog

Toutes les évolutions notables de ce paquet sont consignées ici.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le
versionnement suit [Semantic Versioning](https://semver.org/lang/fr/).

Chaque version publiée porte un tag Git `vX.Y.Z` correspondant à sa section.

## [Non publié]

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
