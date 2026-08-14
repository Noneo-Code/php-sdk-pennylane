# Sécurité

## Transport

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

## Secrets dans les traces

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
