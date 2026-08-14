# Pagination, filtres et tri

## Pagination

Deux entrées, selon que vous pilotez le curseur ou non :

```php
// Une page, curseur à votre main
$page = $pennylane->customerInvoices()->list(limit: 50);
$page->items;      // CustomerInvoiceDto[]
$page->hasMore;    // bool
$page->nextCursor; // ?string

// Toutes les pages, en Generator
foreach ($pennylane->customerInvoices()->paginate() as $invoice) {
    // …
}
```

`paginate()` laisse par défaut Pennylane choisir la taille de page (20). Sur
de gros volumes, demandez-en davantage — la borne haute dépend de l'endpoint
(100 le plus souvent, 1000 pour quelques-uns) :

```php
foreach ($pennylane->ledgerEntries()->paginate(perPage: 100) as $entry) {
    // 5 fois moins de requêtes, donc 5 fois moins de temps sous le quota
}
```

## Filtres et tri

```php
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;

$page = $pennylane->customerInvoices()->list(
    filters: FilterSet::make()
        ->gteq('date', '2026-01-01')
        ->in('customer_id', [12, 34]),
    sort: Sort::desc('date'),
);
```

Les neuf opérateurs de `FilterOperator` sont typés : une faute de frappe est
une erreur de compilation, pas un 422 en production. `paginate()` renvoie
filtre et tri à chaque page — sans quoi Pennylane retourne silencieusement les
pages suivantes non filtrées.

## Limites connues

- Le paramètre `include` de `GET /customer_invoices` n'est pas exposé : il est
  marqué expérimental par Pennylane et sa réponse place les ressources liées
  dans une section `included` que `CursorPage` ne modélise pas. L'exposer
  seul rendrait un résultat inaccessible.
- La borne haute de `limit` n'est pas validée localement : elle vaut 100 sur la
  plupart des endpoints mais 1000 sur quatorze d'entre eux. Un dépassement
  remonte en `BadRequestException`.
