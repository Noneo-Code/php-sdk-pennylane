<?php

/*
 * Côté cabinet : parcourir le portefeuille d'entreprises, puis lire les
 * écritures de l'une d'elles via le scope `company()`.
 *
 * Le jeton attendu ici est un jeton *cabinet* (Firm API v1), distinct du jeton
 * entreprise utilisé par les autres exemples.
 *
 * Usage : PENNYLANE_FIRM_TOKEN=… php examples/firm_portfolio.php
 */

declare(strict_types=1);

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\PennylaneFirm;

require __DIR__.'/../vendor/autoload.php';

$token = getenv('PENNYLANE_FIRM_TOKEN');

if (!is_string($token) || '' === $token) {
    fwrite(STDERR, "Renseignez le jeton cabinet dans PENNYLANE_FIRM_TOKEN.\n");
    exit(1);
}

$firm = new PennylaneFirm($token);

// 1. Le portefeuille. `companies()` pagine par numéro de page, non par curseur :
//    c'est la convention de la Firm API, et `paginate()` la masque.
$portfolio = [];

foreach ($firm->companies()->paginate(perPage: 100) as $company) {
    $portfolio[] = $company;

    printf("#%-8d %-40s SIREN %s\n", $company->id, $company->name, $company->siren);
}

printf("\n%d entreprise(s) au portefeuille.\n\n", count($portfolio));

if ([] === $portfolio) {
    exit(0);
}

// 2. Le scope entreprise. Toutes les requêtes qui en partent sont préfixées
//    `/companies/{id}` ; il hérite du rate limit, des timeouts et de la
//    politique de rejeu du connecteur cabinet.
$company = $portfolio[0];
$scope = $firm->company($company->id);

printf("Dernières écritures de %s :\n", $company->name);

$entries = $scope->ledgerEntries()->list(limit: 10, sort: Sort::desc('date'));

foreach ($entries->items as $entry) {
    printf("  %-12s %-40s journal #%d\n", $entry->date ?? '—', $entry->label, $entry->journal->id);
}

// 3. Une même instance de scope se réutilise pour d'autres groupes de la
//    Firm API — plan comptable, balance, exports, DMS…
$accounts = $scope->ledgerAccounts()->list(limit: 5);

printf("\n%d compte(s) du plan comptable lus sur la première page.\n", count($accounts->items));
