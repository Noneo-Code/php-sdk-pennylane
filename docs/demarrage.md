# Démarrage

Le paquet est **agnostique de tout framework** : il ne dépend ni de Symfony,
ni de Laravel, et s'utilise par simple injection de dépendances.

## Installation

```bash
composer require noneo/sdk-pennylane
```

Requiert PHP `>=8.3` — la contrainte vient des constantes de classe typées
(PHP 8.3) ; rien dans le SDK n'exige 8.4.

## Premier appel

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

`Pennylane` cible la Company API v2 ; le client cabinet, `PennylaneFirm`,
est décrit dans [Firm API](firm.md). L'authentification par OAuth 2.0 a
[sa propre page](oauth.md).

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
