# Développement

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

## Tests d'intégration

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

## Conformité des fixtures aux schémas

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

## Specs OpenAPI

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
