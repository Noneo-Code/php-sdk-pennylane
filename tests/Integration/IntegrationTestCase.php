<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Integration;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\LedgerAccountDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Tests\Support\IntegrationEnvironment;
use PHPUnit\Framework\TestCase;

/**
 * Socle des tests qui parlent à la vraie API Pennylane.
 *
 * Ces tests ne tournent que sur `composer test:integration`, et seulement si
 * `PENNYLANE_API_TOKEN` est présent : sans jeton, chacun s'ignore de lui-même
 * plutôt que d'échouer. Le connecteur est partagé par toute la suite — une
 * seule instance, donc un seul compteur de débit, pour que le throttle 25/5 s
 * protège réellement le quota au lieu d'être remis à zéro à chaque test.
 */
abstract class IntegrationTestCase extends TestCase
{
    private static ?Pennylane $connector = null;

    private static ?IntegrationEnvironment $environment = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (null !== $reason = self::environment()->skipReason()) {
            self::markTestSkipped($reason);
        }
    }

    protected static function environment(): IntegrationEnvironment
    {
        return self::$environment ??= IntegrationEnvironment::fromGlobals();
    }

    protected static function pennylane(): Pennylane
    {
        return self::$connector ??= new Pennylane(self::environment()->token());
    }

    /**
     * À appeler en tête de tout test qui crée ou modifie quoi que ce soit :
     * l'écriture demande `PENNYLANE_SANDBOX=1` en plus du jeton.
     */
    protected function requireWritableSandbox(): void
    {
        if (null !== $reason = self::environment()->writeSkipReason()) {
            self::markTestSkipped($reason);
        }
    }

    /**
     * Marque à apposer dans un champ libre de tout objet créé. L'API ne permet
     * pas de supprimer les objets d'un bac à sable — factures finalisées et
     * écritures comptables notamment — ; ce tag est ce qui permet de les
     * retrouver a posteriori. Le nettoyage reste manuel, par conception.
     */
    protected function runTag(): string
    {
        return IntegrationEnvironment::runTag();
    }

    /**
     * Journal d'accueil des écritures créées par la suite. Le compte de test
     * n'en a pas de connu à l'avance : on prend le premier que l'API rend.
     */
    protected static function firstJournalId(): int
    {
        $journal = self::pennylane()->journals()->list(limit: 1)->items[0] ?? null;

        if (!$journal instanceof JournalDto) {
            self::markTestSkipped('Aucun journal sur ce compte : écriture impossible.');
        }

        return $journal->id;
    }

    /**
     * Deux comptes du plan comptable, pour la contrepartie d'une écriture.
     *
     * @return array{int, int}
     */
    protected static function twoLedgerAccountIds(): array
    {
        $accounts = [];

        foreach (self::pennylane()->ledgerAccounts()->list(limit: 10, filters: FilterSet::make()->eq('enabled', true))->items as $account) {
            if ($account instanceof LedgerAccountDto) {
                $accounts[] = $account->id;
            }
        }

        if (count($accounts) < 2) {
            self::markTestSkipped('Moins de deux comptes actifs : écriture à deux lignes impossible.');
        }

        return [$accounts[0], $accounts[1]];
    }
}
