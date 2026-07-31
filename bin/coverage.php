#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Noneo\Sdk\Pennylane\Tooling\Coverage\CoverageReport;
use Noneo\Sdk\Pennylane\Tooling\Coverage\RequestClassLocator;
use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperationsReader;

$specFiles = [
    __DIR__.'/../pennylane-api-v2.yaml',
    __DIR__.'/../pennylane-api-firm-v1.yaml',
];

$operations = [];

foreach ($specFiles as $specFile) {
    if (!is_file($specFile)) {
        fwrite(STDERR, "Spec introuvable : {$specFile}. Lancez d'abord php bin/fetch-openapi.php.\n");
        exit(1);
    }

    array_push($operations, ...SpecOperationsReader::fromYamlFile($specFile));
}

$coveredBasenames = RequestClassLocator::classBasenames(__DIR__.'/../src/Requests');
$report = new CoverageReport($operations, $coveredBasenames);

fwrite(STDOUT, sprintf("%d/%d endpoints couverts\n", $report->coveredCount(), $report->total()));

if ($report->missing() !== []) {
    fwrite(STDOUT, "\nEndpoints manquants :\n");
    foreach ($report->missing() as $operation) {
        fwrite(STDOUT, sprintf("  - %-6s %-55s (%s)\n", $operation->method, $operation->path, $operation->operationId));
    }
}
