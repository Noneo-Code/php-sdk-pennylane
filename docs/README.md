# Documentation — noneo/sdk-pennylane

SDK PHP pour l'API [Pennylane](https://pennylane.readme.io) — Company API v2
et Firm API v1.

## Guides

### Prise en main

- [Démarrage](demarrage.md) — installation, connecteur, intégration Symfony,
  dépendances
- [Pagination, filtres et tri](pagination.md) — `list()`, `paginate()`,
  `FilterSet`, `Sort`, limites connues
- [Écrire des données](ecrire-des-donnees.md) — mises à jour partielles
  (`Clear`), montants en chaînes décimales

### Domaines

- [Firm API](firm.md) — connecteur cabinet, scope entreprise, DMS
- [Upload de fichiers](fichiers.md) — pièces jointes, import de factures
- [E-invoicing](e-invoicing.md) — émission, réception, immatriculation PA
- [Webhooks](webhooks.md) — abonnements, vérification de signature
- [OAuth 2.0](oauth.md) — `OAuthClient`
- [Tester votre intégration](tester.md) — `MockPennylane`, sans réseau ni
  symbole Saloon

### Exploitation

- [Erreurs, rate limit et timeouts](erreurs.md) — hiérarchie d'exceptions,
  quota, politique de rejeu, bornes de temps
- [Sécurité](securite.md) — transport, secrets dans les traces

### Aller plus loin

- [Exemples exécutables](exemples.md) — les scripts du dossier `examples/`
- [Développement](developpement.md) — scripts composer, tests d'intégration,
  conformité aux specs OpenAPI
