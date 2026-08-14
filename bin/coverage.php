#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Noneo\Sdk\Pennylane\Tooling\Coverage\CoverageReport;
use Noneo\Sdk\Pennylane\Tooling\Coverage\RequestClassLocator;
use Noneo\Sdk\Pennylane\Tooling\Coverage\RequestScope;
use Noneo\Sdk\Pennylane\Tooling\Coverage\ResourceGraph;
use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperationsReader;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecSources;

$operations = [];

foreach (array_keys(SpecSources::URLS) as $file) {
    $specFile = __DIR__.'/../'.$file;

    if (!is_file($specFile)) {
        fwrite(STDERR, "Spec introuvable : {$specFile}. Lancez d'abord php bin/fetch-openapi.php.\n");
        exit(1);
    }

    array_push($operations, ...SpecOperationsReader::fromJsonFile($specFile));
}

// operationId => raison de l'exclusion explicite (endpoint volontairement non implémenté)
$excludedOperationIds = [
    'postLedgerAttachments' => 'Déprécié au profit de postFileAttachments (ticket 18).',
];

$srcDir = __DIR__.'/../src';

// Une opération n'est couverte que si une ressource l'expose depuis le
// connecteur qui porte son chemin : une requête partagée entre la Company API
// et la Firm API peut être joignable d'un côté et pas de l'autre.
$reachableRequests = [
    RequestScope::Company->value => ResourceGraph::requestsReachableFrom($srcDir, 'Pennylane'),
    RequestScope::Firm->value => ResourceGraph::requestsReachableFrom($srcDir, 'PennylaneFirm', barriers: ['CompanyScope']),
    RequestScope::FirmCompany->value => ResourceGraph::requestsReachableFrom($srcDir, 'CompanyScope'),
];

$signatures = RequestClassLocator::signatures($srcDir.'/Requests');
$report = new CoverageReport($operations, $signatures, $excludedOperationIds, $reachableRequests);

fwrite(STDOUT, sprintf("%d/%d endpoints couverts\n", $report->coveredCount(), $report->total()));

if ([] !== $report->missing()) {
    fwrite(STDOUT, "\nEndpoints manquants :\n");
    foreach ($report->missing() as $operation) {
        fwrite(STDOUT, sprintf("  - %-6s %-55s (%s)\n", $operation->method, $operation->path, $operation->operationId));
    }
}

if ([] !== $report->unreachable()) {
    fwrite(STDOUT, "\nEndpoints couverts par une requête qu'aucune ressource n'expose :\n");
    foreach ($report->unreachable() as $operation) {
        fwrite(STDOUT, sprintf("  - %-6s %-55s (%s)\n", $operation->method, $operation->path, $operation->operationId));
    }
}

if ([] !== $report->excluded()) {
    fwrite(STDOUT, "\nExclusions explicites :\n");
    foreach ($report->excluded() as $exclusion) {
        $operation = $exclusion['operation'];
        fwrite(STDOUT, sprintf("  - %-6s %-55s (%s) — %s\n", $operation->method, $operation->path, $operation->operationId, $exclusion['reason']));
    }
}

if ([] !== $report->undocumented()) {
    fwrite(STDOUT, "\nRequêtes sans opération documentée correspondante :\n");
    foreach ($report->undocumented() as $signature) {
        fwrite(STDOUT, sprintf("  - %-40s %-6s %s\n", $signature->basename, $signature->method, $signature->endpoint));
    }
}

if (null !== $summary = $report->failureSummary()) {
    fwrite(STDERR, "\n".$summary."\n");

    exit($report->exitCode());
}
