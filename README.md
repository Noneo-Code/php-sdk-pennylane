# noneo/sdk-pennylane

SDK PHP pour l'API [Pennylane](https://pennylane.readme.io) — Company API v2
et Firm API v1.

> [!IMPORTANT]
> Le dépôt d'origine vit sur
> **<https://gitlab.noneo.fr/noneo/php-packages/php-sdk-pennylane>**. Le dépôt
> GitHub n'en est qu'un **miroir en lecture seule** : issues et merge requests
> se font sur le GitLab.

Le paquet est **agnostique de tout framework** : il ne dépend ni de Symfony,
ni de Laravel, et s'utilise par simple injection de dépendances. Les méthodes
de ressource rendent des DTO typés, jamais une réponse brute ; les montants
voyagent en chaînes décimales, jamais en `float`.

## Installation

```bash
composer require noneo/sdk-pennylane
```

Requiert PHP `>=8.3`.

## Démarrage

Le client s'instancie avec le seul jeton d'API :

```php
use Noneo\Sdk\Pennylane\Pennylane;

$pennylane = new Pennylane($companyToken);

$invoice = $pennylane->customerInvoices()->get(42);
echo $invoice->amount;
```

## Documentation

La documentation complète vit dans [`docs/`](docs/README.md) :

- [Démarrage](docs/demarrage.md) — installation, client, Symfony, dépendances
- [Pagination, filtres et tri](docs/pagination.md)
- [Écrire des données](docs/ecrire-des-donnees.md) — `Clear`, montants
  décimaux
- [Firm API](docs/firm.md) — client cabinet, scope entreprise, DMS
- [Upload de fichiers](docs/fichiers.md)
- [E-invoicing](docs/e-invoicing.md) — émission, réception, immatriculation PA
- [Webhooks](docs/webhooks.md) — abonnements, vérification de signature
- [OAuth 2.0](docs/oauth.md)
- [Tester votre intégration](docs/tester.md) — `MockPennylane`, sans réseau
- [Erreurs, rate limit et timeouts](docs/erreurs.md)
- [Sécurité](docs/securite.md) — transport, secrets dans les traces
- [Exemples exécutables](docs/exemples.md)
- [Développement](docs/developpement.md) — scripts, tests d'intégration,
  conformité OpenAPI

## Développement

```bash
composer install
composer check   # test + stan + cs + schema, comme en CI
```

Le détail des suites de tests et de l'outillage OpenAPI est dans
[docs/developpement.md](docs/developpement.md).

## Licence

MIT
