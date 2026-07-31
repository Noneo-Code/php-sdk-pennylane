---
name: traiter-ticket
description: Utiliser quand Valentin demande de traiter un ticket GitLab du SDK Pennylane (ex. « /traiter-ticket 8 », « traite le ticket #8 »).
---

# Traiter un ticket du SDK Pennylane

Tu traites **UN** ticket de bout en bout, **dans la session courante et dans ce seul contexte**.

Pas de sous-agents, pas de worktree, pas d'orchestration. Un ticket de ce dépôt, c'est un groupe
de ressources HTTP : quelques `Request`, quelques `Dto`, une `Resource`, et leurs tests. Ça tient
dans une tête. Le découpage en phases déléguées coûterait plus qu'il ne rapporte.

## Entrée

`/traiter-ticket <n>` — projet glab `noneo/php-packages/php-sdk-pennylane`.

## Arrêt humain

**Aucun, par défaut.** Le ticket porte déjà sa checklist d'endpoints et ses critères d'acceptation ;
`docs/superpowers/specs/2026-07-31-sdk-pennylane-design.md` porte l'architecture. Tu enchaînes
jusqu'à la MR.

Tu t'arrêtes **uniquement** si le ticket exige une décision que le design n'a pas tranchée. Le
design en laisse trois ouvertes, volontairement :

- la modélisation des clients `company` / `individual` (deux DTO + interface, ou un DTO discriminé) ;
- l'emplacement des quatre endpoints de matching (ressource `Transactions`, ressources factures,
  ou les deux) ;
- l'opportunité d'un helper de polling opt-in pour les exports asynchrones.

Sur ces trois-là, ou sur toute autre divergence d'avec le design : tu proposes, tu attends le 👍.
Pour tout le reste, tu tranches.

## Liste de tâches — fixe, créée en tout premier

Avant même de lire le ticket, crée ces **7 tâches**, libellés exacts, dans cet ordre :

1. `0. Lire le ticket`
2. `1. Branche feat/fix`
3. `2. Lire la spec OpenAPI du groupe`
4. `3. Implémentation (TDD)`
5. `4. Vert confirmé (test + stan + cs)`
6. `5. Couverture + checklist du ticket`
7. `6. Merge request`

Liste **fixe** : ne renomme, ne réordonne, n'ajoute ni ne fusionne jamais — c'est ce qui rend deux
runs comparables. Une seule tâche `in_progress` à la fois. Le détail interne d'une étape va dans
tes messages, jamais en sous-tâches.

## Checklist

- [ ] **0. Lire le ticket.** `glab issue view <n> --repo noneo/php-packages/php-sdk-pennylane`.
  Relever les `operationId` de la checklist, les scopes, les dépendances inter-lots annoncées et
  les points d'attention. Si une dépendance n'est pas livrée, dis-le et propose de traiter le
  ticket bloquant d'abord.

- [ ] **1. Branche.** Depuis `main` à jour : `git switch -c feat/<slug-court>` (`fix/` si le ticket
  corrige). Jamais de commit direct sur `main`. Pas de worktree — ce dépôt n'a pas de stack à
  isoler.

- [ ] **2. Lire la spec OpenAPI du groupe.** `pennylane-api-v2.yaml` (ou
  `pennylane-api-firm-v1.yaml`) est la **source de vérité** locale : chemins exacts, paramètres,
  schémas de réponse, énumérations. Lis les opérations du ticket **avant** d'écrire quoi que ce
  soit. N'invente jamais un champ « probable » — s'il manque dans la spec, il n'existe pas.

  Si la spec est absente ou périmée : `php bin/fetch-openapi.php`.

- [ ] **3. Implémentation (TDD).** Invoque `superpowers:test-driven-development`. Test d'abord,
  toujours.

  Ordre par endpoint : test `MockClient` → `Request` → `Dto` → méthode de `Resource`.

  Conventions du dépôt (cf. design, §4) :
  - `src/Requests/<Groupe>/<Operation>.php`, une classe par endpoint
  - `src/Dto/<Groupe>/<Nom>Dto.php`, `fromArray()` testé sur un payload **copié de la doc**, pas inventé
  - `src/Resource/<Groupe>.php` : les méthodes rendent des **DTO**, jamais un `Response` brut
  - les listes rendent un `CursorPage` ; `paginate()` rend un `Generator`
  - énumérations de l'API → enum backed PHP, jamais `string`
  - aucun paquet `illuminate/*` ni `laravel/*`, même en dev — c'est vérifié en CI
  - commits en français, Conventional Commits

  Un test qui vérifie seulement l'absence d'exception ne compte pas : il doit assurer **la méthode
  HTTP, l'URL, la query et le corps**.

- [ ] **4. Vert confirmé.** `composer check` (phpunit + phpstan max + php-cs-fixer) en avant-plan —
  ça dure des secondes, pas des minutes. Un échec : corrige-le, ne le contourne pas. Résume l'état
  en une ligne (`OK (142 tests)`), pas en mur de logs.

- [ ] **5. Couverture + checklist.** `php bin/coverage.php` doit refléter les endpoints ajoutés.
  Coche la checklist du ticket via `glab issue update`. Un endpoint volontairement non implémenté
  (déprécié, par exemple) se documente comme exclusion explicite — jamais laissé en trou silencieux.

- [ ] **6. Merge request.** Push, puis `glab mr create` vers `main` : titre Conventional Commits,
  description **en français**, **`Closes #<n>`**. Signale dans la description toute décision prise
  en cours de route qui écarte du design.

## Si ça bloque

Tests rouges après tentatives raisonnables, ou blocage de fond : **arrête-toi, n'ouvre pas la MR,
ne pousse pas du cassé.** Livre un rapport court : ce qui a été tenté, l'état exact (dernière
sortie de `composer check`), l'option recommandée pour débloquer. La branche intacte permet la
reprise.

Une divergence d'avec le design n'est pas un échec — c'est un point à faire valider, et la MR
s'ouvre quand même une fois validé.
