<?php

/*
 * Premier contact avec l'API : identité du jeton, liste filtrée et triée,
 * puis parcours complet en pagination.
 *
 * Usage : PENNYLANE_TOKEN=… php examples/quickstart.php
 */

declare(strict_types=1);

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Saloon\Http\Auth\TokenAuthenticator;

require __DIR__.'/../vendor/autoload.php';

$token = getenv('PENNYLANE_TOKEN');

if (!is_string($token) || '' === $token) {
    fwrite(STDERR, "Renseignez le jeton d'API dans PENNYLANE_TOKEN.\n");
    exit(1);
}

$pennylane = new Pennylane(new TokenAuthenticator($token));

// 1. À qui appartient ce jeton, et que permet-il ?
$me = $pennylane->me()->get();

printf("Entreprise : %s (SIREN %s)\n", $me->company->name, $me->company->regNo);
printf("Scopes     : %s\n\n", implode(', ', $me->scopes));

// 2. Une page de factures, filtrée et triée côté serveur.
$page = $pennylane->customerInvoices()->list(
    limit: 20,
    filters: FilterSet::make()->gteq('date', '2026-01-01'),
    sort: Sort::desc('date'),
);

printf("%d facture(s) sur la première page, encore des suivantes : %s\n", count($page->items), $page->hasMore ? 'oui' : 'non');

foreach ($page->items as $invoice) {
    printf("  %-14s %10s %s  [%s]\n", $invoice->invoiceNumber, $invoice->currencyAmount, $invoice->date ?? '—', $invoice->status->value);
}

// 3. Le portefeuille entier, sans piloter le curseur soi-même. `paginate()`
//    rend un Generator : rien n'est chargé en mémoire d'un bloc, et filtres et
//    tri sont repropagés à chaque page.
$total = 0;

foreach ($pennylane->customerInvoices()->paginate(
    filters: FilterSet::make()->eq('status', 'late'),
    sort: Sort::asc('deadline'),
    perPage: 100,
) as $invoice) {
    ++$total;

    if (1 === $total) {
        echo "\nFacture en retard la plus ancienne :\n";
        printf("  %s échue le %s\n", $invoice->invoiceNumber, $invoice->deadline ?? '—');
    }
}

printf("\n%d facture(s) en retard.\n", $total);
