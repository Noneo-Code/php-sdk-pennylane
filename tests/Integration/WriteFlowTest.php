<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Integration;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceStatus;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;

/**
 * Parcours d'écriture, sous double verrou : jeton **et** `PENNYLANE_SANDBOX=1`.
 *
 * Rien n'est nettoyé après coup — l'API ne permet pas de supprimer une facture
 * finalisée ni une écriture comptable. Tous les objets créés portent le tag de
 * run (`sdk-test-…`) dans un champ libre ; c'est par lui qu'on les retrouve
 * dans le bac à sable, et le ménage y reste manuel. N'utilisez jamais ces
 * tests contre une comptabilité de production.
 */
final class WriteFlowTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->requireWritableSandbox();
    }

    /**
     * Le parcours nominal complet : un produit, un client, une facture brouillon
     * qui référence les deux, puis sa finalisation. Chaque étape dépend de
     * l'identifiant rendu par la précédente — un champ mal décodé casse la
     * chaîne ici, pas trois mois plus tard en production.
     */
    public function testItCreatesAProductACustomerAndFinalisesADraftInvoice(): void
    {
        $tag = $this->runTag();
        $pennylane = self::pennylane();

        $product = $pennylane->products()->create(
            label: 'Prestation '.$tag,
            priceBeforeTax: '100.00',
            vatRate: VatRate::Fr200,
            description: $tag,
            unit: 'piece',
        );

        self::assertGreaterThan(0, $product->id);
        self::assertSame('100.00', $product->priceBeforeTax);

        $customer = $pennylane->customers()->createCompany(
            name: 'Client '.$tag,
            billingAddress: new Address('8 rue de la Paix', '75002', 'Paris', 'FR'),
            notes: $tag,
        );

        self::assertGreaterThan(0, $customer->id);

        $invoice = $pennylane->customerInvoices()->create(
            date: date('Y-m-d'),
            deadline: date('Y-m-d', strtotime('+30 days')),
            customerId: $customer->id,
            invoiceLines: [
                new NewStandardInvoiceLine(
                    label: 'Prestation '.$tag,
                    rawCurrencyUnitPrice: '100.00',
                    unit: 'piece',
                    vatRate: VatRate::Fr200,
                    quantity: 1,
                ),
            ],
            draft: true,
            label: $tag,
        );

        self::assertTrue($invoice->draft, 'La facture devait être créée en brouillon.');
        self::assertSame($customer->id, $invoice->customerId);

        $finalised = $pennylane->customerInvoices()->finalize($invoice->id);

        self::assertFalse($finalised->draft, 'La finalisation doit sortir la facture du brouillon.');
        self::assertNotSame(CustomerInvoiceStatus::Draft, $finalised->status);
    }

    /**
     * Une écriture équilibrée : autant au débit qu'au crédit. C'est le seul
     * moyen de vérifier que le SDK envoie les montants dans le format attendu
     * — l'API refuse le moindre écart de centime.
     */
    public function testItCreatesABalancedLedgerEntry(): void
    {
        $pennylane = self::pennylane();

        $journal = self::firstJournalId();
        $accounts = self::twoLedgerAccountIds();

        $entry = $pennylane->ledgerEntries()->create(
            date: date('Y-m-d'),
            label: 'Écriture '.$this->runTag(),
            journalId: $journal,
            ledgerEntryLines: [
                new NewLedgerEntryLine(debit: '120.00', credit: '0.00', ledgerAccountId: $accounts[0]),
                new NewLedgerEntryLine(debit: '0.00', credit: '120.00', ledgerAccountId: $accounts[1]),
            ],
        );

        self::assertGreaterThan(0, $entry->id);
        self::assertNotNull($entry->ledgerEntryLines);
        self::assertCount(2, $entry->ledgerEntryLines);
    }

    public function testItUploadsAFileAttachment(): void
    {
        $attachment = self::pennylane()->fileAttachments()->upload(
            self::minimalPdf(),
            $this->runTag().'.pdf',
        );

        self::assertGreaterThan(0, $attachment->id);
        self::assertStringEndsWith('.pdf', $attachment->filename);
        self::assertNotSame('', $attachment->url);
    }

    /**
     * Le plus petit PDF valide qui soit — l'API refuse un fichier dont elle ne
     * reconnaît pas le type, et embarquer un fichier binaire dans le dépôt pour
     * un seul test n'en vaut pas la peine.
     */
    private static function minimalPdf(): string
    {
        return "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            ."2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R>>endobj\n"
            ."trailer<</Root 1 0 R>>\n%%EOF\n";
    }
}
