# Firm API

`PennylaneFirm` cible le cabinet ; `company()` rend un scope dont les requêtes
partent sur `/companies/{id}/…`. Le préfixe est porté par le connecteur, donc
une même instance de requête peut être réutilisée, y compris d'une entreprise
à l'autre.

```php
use Noneo\Sdk\Pennylane\PennylaneFirm;

$firm = new PennylaneFirm($firmToken);

foreach ($firm->companies()->paginate() as $company) {
    $firm->company($company->id)->ledgerEntries()->list(limit: 10);
}
```

Le scope hérite du rate limit, des timeouts et de la politique de rejeu de son
`PennylaneFirm`. Il expose aussi le DMS du cabinet, absent de la Company API :

```php
$firm->company(1234)->dms()->uploadFile($handle, name: 'bilan-2026.pdf');
```

Les factures d'une entreprise se lisent depuis le scope. Ces deux ressources
sont **en lecture seule** : la Firm API n'expose que la liste et le détail, là
où la Company API accepte aussi la création, l'import ou la validation
comptable.

```php
$firm->company(1234)->supplierInvoices()->list(limit: 50);
$firm->company(1234)->customerInvoices()->get(4242);
```

Les changelogs du scope suivent les mêmes groupes, factures comprises :

```php
$firm->company(1234)->changelogs()->supplierInvoices(startDate: '2026-01-01T00:00:00Z');

foreach ($firm->company(1234)->changelogs()->paginateCustomerInvoices() as $change) {
    $change->id;
}
```

L'arborescence de dossiers du DMS est décrite dans
[Upload de fichiers](fichiers.md).
