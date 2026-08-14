# Upload de fichiers

`fileAttachments()->upload()` accepte indifféremment une **ressource stream** ou
une chaîne. Préférez le stream : un PDF de plusieurs mégaoctets ne transite
alors jamais entier par la mémoire du process.

```php
$handle = fopen('/chemin/facture.pdf', 'r');

$attachment = $pennylane->fileAttachments()->upload($handle, filename: 'facture.pdf');

fclose($handle);

$attachment->id;  // à passer à customerInvoices()->import(...)
$attachment->url; // lien de téléchargement signé
```

L'usage principal est l'import d'une facture déjà émise ailleurs : on téléverse
le PDF, puis on rattache l'identifiant obtenu au document.

```php
$pennylane->customerInvoices()->import(
    fileAttachmentId: $attachment->id,
    date: '2026-01-31',
    deadline: '2026-03-01',
    customerId: 42,
    currencyAmountBeforeTax: '1000.00',
    currencyAmount: '1200.00',
    currencyTax: '200.00',
    invoiceLines: [],
);
```

Côté cabinet, le DMS a sa propre arborescence de dossiers :

```php
$folder = $firm->company(1234)->dms()->createFolder('Exercice 2026');
$firm->company(1234)->dms()->uploadFile($handle, name: 'grand-livre.pdf', parentFolderId: $folder->id);
```

L'import d'une facture **électronique** (Factur-X, UBL, CII) passe par un
flux dédié, décrit dans [E-invoicing](e-invoicing.md).
