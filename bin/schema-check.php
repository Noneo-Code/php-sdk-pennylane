#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaLocator;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaReport;
use Noneo\Sdk\Pennylane\Tooling\Schema\TargetScanner;

/**
 * Confronte les fixtures des tests et les enums du SDK aux schémas OpenAPI
 * committés.
 *
 * L'outil de couverture (`bin/coverage.php`) valide les *chemins* : il dit que
 * chaque endpoint documenté a une requête. Celui-ci valide les *formes* : que
 * les payloads recopiés à la main dans les tests décrivent bien ce que l'API
 * rend, et que chaque enum backed couvre exactement les valeurs de la spec.
 * Sans lui, Pennylane peut renommer ou retyper un champ sans que rien ne le
 * signale — les 963 tests unitaires valideraient toujours l'ancienne forme.
 */
$root = dirname(__DIR__);

// sujet => raison de l'écart assumé. Un écart connu se documente ici plutôt
// que de faire échouer la CI ; une entrée sans cible correspondante ne
// dispense de rien et disparaît d'elle-même du rapport.
$exclusions = [
    // Pennylane écrit ces deux ensembles comme des `oneOf` discriminés : chaque
    // branche porte le champ avec une énumération à *une* valeur. Le SDK les
    // réunit en un seul enum, ce qui est la bonne modélisation côté PHP mais
    // n'a, par construction, aucun équivalent exact dans la spec.
    Noneo\Sdk\Pennylane\Dto\Customer\CustomerType::class => 'Discriminant d\'un oneOf : la spec l\'écrit en une valeur par branche (company, individual).',
    Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceStatusTransition::class => 'Discriminant d\'un oneOf : la spec l\'écrit en une valeur par branche (disputed, refused, approved).',

    // L'enveloppe d'un webhook n'est le corps d'aucune opération : la spec ne
    // décrit que ce que l'API expose en HTTP, pas ce qu'elle émet vers nous.
    Noneo\Sdk\Pennylane\Tests\Webhook\WebhookEventTest::class.'::documentedPayload' => 'Enveloppe de webhook : émise par Pennylane, absente d\'une spec qui ne décrit que des endpoints.',
];

$testClasses = [
    ...TargetScanner::classesIn($root.'/tests/Dto', 'Noneo\Sdk\Pennylane\Tests\Dto'),
    ...TargetScanner::classesIn($root.'/tests/Webhook', 'Noneo\Sdk\Pennylane\Tests\Webhook'),
];

$payloads = TargetScanner::payloadsIn($testClasses);
$orphans = TargetScanner::unannotatedPayloadsIn($testClasses);
$enums = TargetScanner::enumsIn(TargetScanner::classesIn($root.'/src/Dto', 'Noneo\Sdk\Pennylane\Dto'));

$report = new SchemaReport(SchemaLocator::fromRepository($root), $payloads, $enums, $exclusions, $orphans);

fwrite(STDOUT, sprintf(
    "%d cibles conformes sur %d (%d payloads, %d enums)\n",
    $report->checkedCount() - count($report->divergences()),
    $report->checkedCount(),
    count($payloads),
    count($enums),
));

if ([] !== $report->divergences()) {
    fwrite(STDOUT, "\nDivergences :\n");
    foreach ($report->divergences() as $divergence) {
        fwrite(STDOUT, sprintf("  - %s\n", $divergence->subject));
        foreach ($divergence->errors as $error) {
            fwrite(STDOUT, sprintf("      %s\n", $error));
        }
    }
}

if ([] !== $report->excluded()) {
    fwrite(STDOUT, "\nExclusions explicites :\n");
    foreach ($report->excluded() as $exclusion) {
        fwrite(STDOUT, sprintf("  - %s — %s\n", $exclusion['subject'], $exclusion['reason']));
    }
}

if (null !== $summary = $report->failureSummary()) {
    fwrite(STDERR, "\n".$summary."\n");

    exit($report->exitCode());
}
