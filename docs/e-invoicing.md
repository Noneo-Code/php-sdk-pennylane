# E-invoicing

La réforme de la facturation électronique passe par une plateforme agréée (PA).
Le SDK couvre les deux sens de circulation.

## Émission

Une facture électronique s'importe depuis un fichier Factur-X, UBL ou CII ;
`sendToPa` décide si elle part dans la foulée ou attend un ordre explicite.

```php
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;

$import = $pennylane->customerInvoices()->importEInvoice(
    fileContents: $handle,
    filename: 'facture.pdf',
    sendToPa: false,
    invoiceOptions: new EInvoiceImportOptions(customerId: 42),
);

$pennylane->customerInvoices()->sendToPa($import->id);
```

Pennylane garde un import générique, `eInvoices()->import()`, où le sens est
porté par un `EInvoiceImportType` plutôt que par la ressource appelée. Il est
**déprécié** et n'accepte aucune option d'import : préférez les deux méthodes
spécialisées ci-dessus.

```php
use Noneo\Sdk\Pennylane\Dto\EInvoice\EInvoiceImportType;

$import = $pennylane->eInvoices()->import(
    fileContents: $handle,
    type: EInvoiceImportType::Supplier,
    filename: 'facture.pdf',
);
```

Le statut vit ensuite sur la facture, dans `eInvoicingStatus` — un
`EInvoicingStatus` : `submitted`, `sent`, `accepted`, `rejected`, `in_dispute`,
`collected`… `eInvoicingReason` porte le motif d'un refus.

## Réception

Les factures fournisseur arrivent en `waiting_for_validation`
(`SupplierEInvoicingStatus`) et attendent une décision, chacune avec son
énumération de motifs :

```php
$pennylane->supplierInvoices()->approveEInvoice(42);
$pennylane->supplierInvoices()->disputeEInvoice(42, SupplierEInvoiceDisputeReason::WrongAmount);
$pennylane->supplierInvoices()->refuseEInvoice(42, SupplierEInvoiceRefusalReason::Other);
```

## Immatriculation

Rien ne circule tant que l'entreprise n'est pas enregistrée auprès de la
plateforme. `paRegistrations()` en donne l'état, par SIREN et par sens
d'échange :

```php
foreach ($pennylane->paRegistrations()->list()->items as $registration) {
    $registration->status;            // PaRegistrationStatus::Activated, Pending…
    $registration->exchangeDirection; // Emission, Reception, EmissionAndReception
}
```

La transmission étant asynchrone, ne sondez pas l'API : abonnez-vous à
l'événement `customer_invoice.e_invoicing_status_updated` — voir
[Webhooks](webhooks.md).
