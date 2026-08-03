#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperationsReader;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\CurlHttpFetcher;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecDownloader;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecJsonWriter;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecSources;

$downloader = new SpecDownloader(new CurlHttpFetcher(delayMicroseconds: 0));

foreach (SpecSources::URLS as $file => $url) {
    fwrite(STDOUT, "Récupération de {$url}...\n");

    $spec = $downloader->download($url);

    file_put_contents(__DIR__.'/../'.$file, SpecJsonWriter::toJson($spec));

    fwrite(STDOUT, sprintf("  %d opération(s) écrite(s) dans %s\n", count(SpecOperationsReader::fromArray($spec)), $file));
}
