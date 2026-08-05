<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Integration;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\Exception\UnauthorizedException;
use Noneo\Sdk\Pennylane\Exception\ValidationException;
use Noneo\Sdk\Pennylane\Pennylane;
use Saloon\Http\Auth\TokenAuthenticator;

/**
 * La hiérarchie d'exceptions confrontée aux vraies réponses d'erreur.
 *
 * Les tests unitaires prouvent que *si* l'API rend un 404, le SDK lève une
 * `NotFoundException`. Ils ne prouvent pas que l'API rend bien un 404 là où on
 * l'attend, ni que son corps d'erreur a la forme que `PennylaneException`
 * sait décoder. C'est ce que ces tests-ci vérifient.
 */
final class ErrorHandlingTest extends IntegrationTestCase
{
    /**
     * Un identifiant hors de toute séquence plausible : la seule façon de
     * provoquer un 404 sans dépendre du contenu du compte.
     */
    private const UNREACHABLE_ID = 2_147_483_647;

    public function testAnUnknownInvoiceRaisesANotFoundException(): void
    {
        try {
            self::pennylane()->customerInvoices()->get(self::UNREACHABLE_ID);

            self::fail('L\'API a rendu une facture pour un identifiant censé ne pas exister.');
        } catch (NotFoundException $exception) {
            self::assertSame(404, $exception->status());
            self::assertNotSame('', $exception->error(), 'Un 404 doit porter un message exploitable.');
        }
    }

    public function testAnInvalidTokenRaisesAnUnauthorizedException(): void
    {
        $connector = new Pennylane(new TokenAuthenticator('invalide-'.$this->runTag()));

        try {
            $connector->me()->get();

            self::fail('L\'API a accepté un jeton fabriqué de toutes pièces.');
        } catch (UnauthorizedException $exception) {
            self::assertSame(401, $exception->status());
        }
    }

    /**
     * Une écriture déséquilibrée : le cas de 422 le plus net que l'API offre.
     * Écriture, donc sous double verrou.
     */
    public function testAnUnbalancedLedgerEntryRaisesAValidationException(): void
    {
        $this->requireWritableSandbox();

        $accounts = self::twoLedgerAccountIds();

        try {
            self::pennylane()->ledgerEntries()->create(
                date: date('Y-m-d'),
                label: 'Déséquilibre '.$this->runTag(),
                journalId: self::firstJournalId(),
                ledgerEntryLines: [
                    new NewLedgerEntryLine(debit: '120.00', credit: '0.00', ledgerAccountId: $accounts[0]),
                    new NewLedgerEntryLine(debit: '0.00', credit: '110.00', ledgerAccountId: $accounts[1]),
                ],
            );

            self::fail('L\'API a accepté une écriture dont le débit et le crédit diffèrent.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->status());
        }
    }
}
