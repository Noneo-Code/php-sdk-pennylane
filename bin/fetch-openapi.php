#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Noneo\Sdk\Pennylane\Tooling\OpenApi\CurlHttpFetcher;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\OpenApiSpecBuilder;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\YamlSpecWriter;

$targets = [
    'https://pennylane.readme.io/llms.txt' => __DIR__.'/../pennylane-api-v2.yaml',
    'https://firm-pennylane.readme.io/llms.txt' => __DIR__.'/../pennylane-api-firm-v1.yaml',
];

$builder = new OpenApiSpecBuilder(new CurlHttpFetcher());

foreach ($targets as $llmsTxtUrl => $outputPath) {
    fwrite(STDOUT, "Récupération de {$llmsTxtUrl}...\n");

    $result = $builder->build($llmsTxtUrl);

    file_put_contents($outputPath, YamlSpecWriter::toYaml($result->spec));

    $operationCount = 0;
    foreach ($result->spec['paths'] as $operations) {
        $operationCount += count($operations);
    }

    fwrite(STDOUT, sprintf("  %d opération(s) écrite(s) dans %s\n", $operationCount, basename($outputPath)));

    if ($result->skippedUrls !== []) {
        fwrite(STDOUT, sprintf("  %d page(s) ignorée(s) (pas de fragment OpenAPI) :\n", count($result->skippedUrls)));
        foreach ($result->skippedUrls as $skippedUrl) {
            fwrite(STDOUT, "    - {$skippedUrl}\n");
        }
    }
}
