# OAuth 2.0

`Auth\OAuthClient` ne fait que le ballet OAuth et rend un
`Auth\AccessToken` — un DTO du SDK (`token`, `refreshToken`, `expiresAt`) à
persister tel quel côté application :

```php
use Noneo\Sdk\Pennylane\Auth\OAuthClient;
use Noneo\Sdk\Pennylane\Pennylane;

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

`OAuthClient` applique les mêmes invariants de transport que les clients
métier (HTTPS obligatoire, redirections non suivies) et accepte les mêmes
réglages de timeouts — voir [Sécurité](securite.md) et
[Erreurs, rate limit et timeouts](erreurs.md).
