<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Integration;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Requests\Me\GetMe;

/**
 * Balayage en lecture seule : aucun de ces tests ne crée, ne modifie ni ne
 * supprime quoi que ce soit, ils tournent donc dès qu'un jeton est présent,
 * bac à sable ou non.
 *
 * Ce qu'ils vérifient et que les mocks ne peuvent pas vérifier : que les
 * `fromArray()` des DTO acceptent les formes de réponse *réelles*, et que les
 * paramètres que le SDK envoie sont bien ceux que l'API attend.
 */
final class ReadFlowTest extends IntegrationTestCase
{
    public function testMeReturnsTheAuthenticatedCompanyAndItsScopes(): void
    {
        $me = self::pennylane()->me()->get();

        self::assertGreaterThan(0, $me->company->id);
        self::assertNotSame('', $me->company->name);
        self::assertNotSame([], $me->scopes, 'Un jeton sans scope ne permettrait aucun des tests suivants.');
    }

    public function testCustomerInvoicesListDecodesEveryItemOfTheFirstPage(): void
    {
        $page = self::pennylane()->customerInvoices()->list(limit: 5);

        self::assertLessThanOrEqual(5, count($page->items));
        self::assertContainsOnlyInstancesOf(CustomerInvoiceDto::class, $page->items);

        if ($page->hasMore) {
            self::assertNotNull($page->nextCursor, 'Une page annonçant "has_more" doit porter un curseur.');
        }
    }

    public function testLedgerEntriesListDecodesEveryItemOfTheFirstPage(): void
    {
        $page = self::pennylane()->ledgerEntries()->list(limit: 5);

        self::assertLessThanOrEqual(5, count($page->items));
        self::assertContainsOnlyInstancesOf(LedgerEntryDto::class, $page->items);
    }

    /**
     * Pagination réelle sur deux pages : le curseur rendu par la première page
     * doit être accepté par l'API, et la seconde page doit porter d'autres
     * factures. Un curseur mal transmis rendrait ici deux fois la même page.
     */
    public function testCustomerInvoicesPaginationWalksToASecondPage(): void
    {
        $invoices = self::pennylane()->customerInvoices();

        $first = $invoices->list(limit: 2);

        if (!$first->hasMore) {
            self::markTestSkipped('Moins de trois factures client sur ce compte : pagination non observable.');
        }

        $second = $invoices->list(cursor: $first->nextCursor, limit: 2);

        self::assertNotSame([], $second->items);
        self::assertSame(
            [],
            array_intersect(self::idsOf($first->items), self::idsOf($second->items)),
            'La seconde page rejoue la première : le curseur n\'a pas été transmis.',
        );
    }

    public function testLedgerEntriesPaginationWalksToASecondPage(): void
    {
        $ledgerEntries = self::pennylane()->ledgerEntries();

        $first = $ledgerEntries->list(limit: 2);

        if (!$first->hasMore) {
            self::markTestSkipped('Moins de trois écritures sur ce compte : pagination non observable.');
        }

        $second = $ledgerEntries->list(cursor: $first->nextCursor, limit: 2);

        self::assertNotSame([], $second->items);
        self::assertSame(
            [],
            array_intersect(self::idsOf($first->items), self::idsOf($second->items)),
            'La seconde page rejoue la première : le curseur n\'a pas été transmis.',
        );
    }

    /**
     * Le test qui aurait attrapé le bug « filtre non rejoué en page 2 » :
     * l'API réapplique `filter` à chaque page, donc une page suivante envoyée
     * sans le paramètre revient *non filtrée* — sans erreur, sans signal. On
     * traverse plusieurs pages avec une limite volontairement basse et on
     * vérifie chaque item, pas seulement les premiers.
     */
    public function testAGteqDateFilterStaysAppliedAcrossPages(): void
    {
        $since = '2000-01-01';
        $filters = FilterSet::make()->gteq('date', $since);

        $seen = 0;

        foreach (self::pennylane()->customerInvoices()->paginate($filters, Sort::asc('date'), perPage: 2) as $invoice) {
            self::assertNotNull($invoice->date, 'Une facture filtrée sur la date doit en porter une.');
            self::assertGreaterThanOrEqual($since, $invoice->date, sprintf(
                'Facture %d datée du %s : le filtre gteq(date, %s) n\'a pas été rejoué.',
                $invoice->id,
                $invoice->date,
                $since,
            ));

            // Trois pages suffisent à prouver que le filtre survit au curseur ;
            // dérouler tout l'historique brûlerait le quota pour rien.
            if (++$seen >= 6) {
                break;
            }
        }

        // Un compte sans facture ne prouve ni que le filtre est rejoué, ni
        // qu'il ne l'est pas : c'est une absence de données, pas une
        // régression. On s'ignore, comme les tests de pagination au-dessus.
        if (0 === $seen) {
            self::markTestSkipped('Aucune facture postérieure à 2000 : filtre non observable.');
        }
    }

    /**
     * Les changelogs n'acceptent pas `start_date` et `cursor` ensemble : le SDK
     * abandonne `start_date` dès la seconde page. Ce test vérifie que le
     * contournement passe encore côté API — une 400 ici signalerait que
     * Pennylane a changé la règle.
     *
     * La spec impose deux contraintes sur `start_date`, toutes deux rendues par
     * l'API sous forme d'erreur et non de résultat vide : le format RFC3339
     * — une date nue vaut 400 — et la fenêtre de rétention de quatre semaines,
     * au-delà de laquelle l'API répond « Start date is too old » en 422. D'où
     * une date relative : une constante finirait par sortir de la fenêtre.
     */
    public function testChangelogsAcceptTheStartDateWorkaround(): void
    {
        $startDate = date('c', strtotime('-7 days'));

        $page = self::pennylane()->changelogs()->customerInvoices(limit: 2, startDate: $startDate);

        self::assertLessThanOrEqual(2, count($page->items));

        if (!$page->hasMore) {
            return;
        }

        // Seconde page : sans `start_date`, avec le curseur seul.
        $next = self::pennylane()->changelogs()->customerInvoices(cursor: $page->nextCursor, limit: 2);

        self::assertLessThanOrEqual(2, count($next->items));
    }

    /**
     * Le throttle local est calé sur 25 requêtes / 5 s. Si Pennylane annonce
     * un autre quota dans ses en-têtes, `resolveLimits()` est à revoir.
     */
    public function testTheApiAnnouncesItsRateLimitHeaders(): void
    {
        $response = self::pennylane()->send(new GetMe());

        $limit = $response->header('RateLimit-Limit') ?? $response->header('X-RateLimit-Limit');

        if (!is_string($limit)) {
            self::markTestSkipped('L\'API n\'annonce aucun en-tête de quota sur cette réponse.');
        }

        self::assertMatchesRegularExpression('/\d+/', $limit);
    }

    /**
     * @param mixed[] $items
     *
     * @return list<int>
     */
    private static function idsOf(array $items): array
    {
        return array_values(array_map(
            static fn (mixed $item): int => is_object($item) && property_exists($item, 'id') && is_int($item->id) ? $item->id : 0,
            $items,
        ));
    }
}
