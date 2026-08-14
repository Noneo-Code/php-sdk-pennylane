<?php

/*
 * Cycle de vie complet d'une facture client : création du client, brouillon,
 * finalisation, envoi par e-mail, marquage payé.
 *
 * Ce script écrit dans l'entreprise visée par le jeton — à jouer sur un
 * environnement de test.
 *
 * Usage : PENNYLANE_TOKEN=… php examples/invoicing_lifecycle.php
 */

declare(strict_types=1);

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Exception\PennylaneException;
use Noneo\Sdk\Pennylane\Pennylane;

require __DIR__.'/../vendor/autoload.php';

$token = getenv('PENNYLANE_TOKEN');

if (!is_string($token) || '' === $token) {
    fwrite(STDERR, "Renseignez le jeton d'API dans PENNYLANE_TOKEN.\n");
    exit(1);
}

$pennylane = new Pennylane($token);

try {
    // 1. Le client destinataire. `createCompany()` et `createIndividual()` sont
    //    deux méthodes distinctes : les champs obligatoires diffèrent.
    $customer = $pennylane->customers()->createCompany(
        name: 'ACME SAS',
        billingAddress: new Address(
            address: '12 rue de la Paix',
            postalCode: '75002',
            city: 'Paris',
            countryAlpha2: 'FR',
        ),
        vatNumber: 'FR12345678901',
        paymentConditions: PaymentConditions::ThirtyDays,
        emails: ['compta@acme.example'],
        billingLanguage: BillingLanguage::FrFr,
    );

    printf("Client créé : #%d %s\n", $customer->id, $customer->name);

    // 2. Un brouillon : modifiable, sans numéro définitif ni écriture comptable.
    $invoice = $pennylane->customerInvoices()->create(
        date: date('Y-m-d'),
        deadline: date('Y-m-d', strtotime('+30 days')),
        customerId: $customer->id,
        invoiceLines: [
            new NewStandardInvoiceLine(
                label: 'Prestation de conseil',
                rawCurrencyUnitPrice: '750.00',
                unit: 'jour',
                vatRate: VatRate::Fr200,
                quantity: 4,
                substance: Substance::Services,
            ),
        ],
        draft: true,
        currency: Currency::Eur,
        pdfInvoiceSubject: 'Mission de janvier',
    );

    printf("Brouillon créé : #%d (%s)\n", $invoice->id, $invoice->status->value);

    // 3. Finalisation : la facture reçoit son numéro et devient immuable.
    $invoice = $pennylane->customerInvoices()->finalize($invoice->id);

    printf("Facture finalisée : %s pour %s %s\n", $invoice->invoiceNumber, $invoice->currencyAmount, $invoice->currency->value);

    // 4. Envoi. Sans destinataires, Pennylane utilise ceux du client.
    $pennylane->customerInvoices()->sendByEmail($invoice->id, ['compta@acme.example']);

    echo "Facture envoyée par e-mail.\n";

    // 5. Encaissement constaté à la main, faute de rapprochement bancaire.
    $pennylane->customerInvoices()->markAsPaid($invoice->id);

    printf("Facture %s marquée payée.\n", $invoice->invoiceNumber);
} catch (PennylaneException $e) {
    // Toutes les erreurs de l'API héritent de cette classe : un seul catch
    // couvre 4xx comme 5xx, et la réponse d'origine reste accessible.
    fwrite(STDERR, sprintf("Erreur Pennylane (%s) : %s\n", $e->errorCode() ?? 'sans code', $e->message()));
    exit(1);
}
