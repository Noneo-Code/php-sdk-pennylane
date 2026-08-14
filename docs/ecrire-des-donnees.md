# Écrire des données

## Mises à jour partielles

Un champ laissé à `null` n'est pas envoyé : Pennylane conserve sa valeur.
Pour **vider** un champ, passez `Clear::value()`, qui envoie un `null`
explicite.

```php
use Noneo\Sdk\Pennylane\Http\Clear;

$pennylane->suppliers()->update(
    id: 42,
    establishmentNo: Clear::value(), // "establishment_no": null — le champ est vidé
    iban: null,                      // absent du corps — l'IBAN est conservé
);
```

(`Unset` aurait été le nom naturel, mais c'est un mot réservé du langage.)

## Montants : jamais de `float`

Pennylane attend ses montants en **chaînes décimales** (`"120.50"`), et le SDK
refuse tout `float`, dans les corps de requête comme dans les query et les
filtres :

```php
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;

new NewProductInvoiceLine(
    productId: 42,
    quantity: '2.5',              // une quantité fractionnaire est une chaîne
    rawCurrencyUnitPrice: '120.50',
);

// InvalidArgumentException : « Le champ « raw_currency_unit_price » a reçu le
// float 120.5. Pennylane attend les montants en chaîne décimale… »
new NewProductInvoiceLine(productId: 42, quantity: 1, rawCurrencyUnitPrice: 120.50);
```

Un `float` PHP ne représente pas exactement un décimal : `json_encode(120.50)`
écrit `120.5`, et `json_encode(0.1 + 0.2)` écrit `0.30000000000000004`. Une
écriture comptable fausse d'un centime est le pire défaut possible pour ce SDK
— silencieuse, et comptablement fausse. Mieux vaut une exception à l'appel
qu'un grand livre à corriger.

La règle **n'a aucune exception** : `2.0` est un `float` comme un autre, y
compris pour une quantité, que l'API type pourtant en `number`. Une règle sans
exception est la seule qui s'explique, et la seule qui ne laisse pas de trou.
Les entiers (`quantity: 3`) restent bien sûr acceptés.

Le contrôle s'applique à toute profondeur — un montant niché dans
`invoice_lines[0]` est trouvé — et le message cite le chemin complet du champ
fautif.
