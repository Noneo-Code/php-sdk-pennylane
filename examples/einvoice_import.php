<?php

/*
 * E-invoicing : importer une facture électronique (Factur-X, UBL, CII) et
 * suivre son statut auprès de la plateforme agréée.
 *
 * Usage : PENNYLANE_TOKEN=… php examples/einvoice_import.php chemin/facture.pdf
 */

declare(strict_types=1);

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Exception\PennylaneException;
use Noneo\Sdk\Pennylane\Pennylane;

require __DIR__.'/../vendor/autoload.php';

$token = getenv('PENNYLANE_TOKEN');

if (!is_string($token) || '' === $token) {
    fwrite(STDERR, "Renseignez le jeton d'API dans PENNYLANE_TOKEN.\n");
    exit(1);
}

$path = $argv[1] ?? null;

if (!is_string($path) || !is_file($path)) {
    fwrite(STDERR, "Usage : php examples/einvoice_import.php chemin/facture.pdf\n");
    exit(1);
}

$pennylane = new Pennylane($token);

try {
    // 1. L'état de l'entreprise auprès de la plateforme agréée. Sans
    //    immatriculation « activated » en émission, l'envoi sera refusé.
    foreach ($pennylane->paRegistrations()->list()->items as $registration) {
        printf(
            "Immatriculation PA : SIREN %s, %s, statut %s\n",
            $registration->siren,
            $registration->exchangeDirection->value,
            $registration->status->value,
        );
    }

    // 2. L'import. Le SDK accepte une ressource stream — le fichier n'est
    //    jamais chargé en mémoire d'un bloc.
    $handle = fopen($path, 'r');

    if (false === $handle) {
        fwrite(STDERR, sprintf("Impossible d'ouvrir « %s ».\n", $path));
        exit(1);
    }

    $import = $pennylane->customerInvoices()->importEInvoice(
        fileContents: $handle,
        filename: basename($path),
        // `true` transmet la facture à la plateforme agréée dans la foulée ;
        // `false` la garde en attente d'un `sendToPa()` explicite.
        sendToPa: false,
        // Ce que le fichier ne dit pas — ou dit mal — se corrige ici.
        invoiceOptions: new EInvoiceImportOptions(customerId: null),
    );

    fclose($handle);

    printf("\nFacture importée : #%d (%s)\n", $import->id, $import->url);

    // 3. Le statut e-invoicing vit sur la facture elle-même.
    $invoice = $pennylane->customerInvoices()->get($import->id);

    printf(
        "Statut e-invoicing : %s%s\n",
        $invoice->eInvoicingStatus?->value ?? 'non transmis',
        null !== $invoice->eInvoicingReason ? ' — '.$invoice->eInvoicingReason : '',
    );

    // 4. Transmission explicite. L'appel est asynchrone : le statut passera par
    //    `submitted` puis `sent`, `accepted`, `rejected`… Plutôt que de sonder
    //    l'API en boucle, abonnez-vous à l'événement
    //    `customer_invoice.e_invoicing_status_updated` (voir webhook_receiver.php).
    if (null === $invoice->eInvoicingStatus) {
        $pennylane->customerInvoices()->sendToPa($invoice->id);

        echo "Facture transmise à la plateforme agréée.\n";
    }

    // Côté fournisseur, le trajet est inverse : les factures reçues arrivent en
    // `waiting_for_validation` et attendent une décision explicite.
    //
    //     $pennylane->supplierInvoices()->approveEInvoice($id);
    //     $pennylane->supplierInvoices()->disputeEInvoice($id, $reason);
    //     $pennylane->supplierInvoices()->refuseEInvoice($id, $reason);
} catch (PennylaneException $e) {
    fwrite(STDERR, sprintf("Erreur Pennylane (%s) : %s\n", $e->errorCode() ?? 'sans code', $e->message()));
    exit(1);
}
