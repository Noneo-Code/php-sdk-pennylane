# Webhooks

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

## Vérifier et décoder un événement

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

## Deux schémas de signature, et pourquoi

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
