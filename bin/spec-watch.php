#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperationsReader;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\CurlHttpFetcher;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\OperationDiff;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecDownloader;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecSources;

/*
 * Compare les specs committées à celles que Pennylane publie, et ouvre une issue
 * quand elles divergent. Prévu pour un pipeline planifié : sans token GitLab, le
 * script se contente d'afficher le rapport, ce qui le rend testable en local.
 */

const ISSUE_TITLE = 'spec-watch : dérive des specs OpenAPI Pennylane';
const ISSUE_LABEL = 'spec-watch';

$downloader = new SpecDownloader(new CurlHttpFetcher(delayMicroseconds: 0));

$sections = [];

foreach (SpecSources::URLS as $file => $url) {
    $diff = OperationDiff::between(
        SpecOperationsReader::fromJsonFile(__DIR__.'/../'.$file),
        SpecOperationsReader::fromArray($downloader->download($url)),
    );

    fwrite(STDOUT, sprintf(
        "%s : %s\n",
        $file,
        $diff->hasDrift()
            ? sprintf('%d ajoutée(s), %d supprimée(s), %d modifiée(s)', count($diff->added), count($diff->removed), count($diff->changed))
            : 'à jour',
    ));

    if ($diff->hasDrift()) {
        $sections[] = sprintf("## `%s`\n\nSource : %s\n\n%s", $file, $url, $diff->toMarkdown());
    }
}

if ([] === $sections) {
    fwrite(STDOUT, "Aucune dérive : rien à signaler.\n");

    exit(0);
}

$body = "Les specs OpenAPI publiées par Pennylane ne correspondent plus à celles committées.\n\n"
    .implode("\n\n", $sections)
    ."\n\nRégénérez-les avec `php bin/fetch-openapi.php`, puis relancez `php bin/coverage.php`.";

fwrite(STDOUT, "\n".$body."\n");

$apiUrl = getenv('CI_API_V4_URL');
$projectId = getenv('CI_PROJECT_ID');
$token = getenv('SPEC_WATCH_TOKEN');

if (!is_string($apiUrl) || !is_string($projectId) || !is_string($token) || '' === $token) {
    fwrite(STDOUT, "\nCI_API_V4_URL, CI_PROJECT_ID ou SPEC_WATCH_TOKEN absent : aucune issue ouverte.\n");

    exit(0);
}

$issuesUrl = sprintf('%s/projects/%s/issues', $apiUrl, rawurlencode($projectId));

/**
 * @param non-empty-string      $method
 * @param array<string, string> $payload
 *
 * @return array<int|string, mixed>
 */
function gitlabApi(string $method, string $url, string $token, array $payload = []): array
{
    $handle = curl_init($url);

    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['PRIVATE-TOKEN: '.$token, 'Content-Type: application/json'],
    ]);

    if ([] !== $payload) {
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
    }

    $response = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

    if (!is_string($response) || $status >= 400) {
        throw new RuntimeException(sprintf('Appel GitLab %s %s en échec : HTTP %d %s', $method, $url, $status, curl_error($handle)));
    }

    $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

    return is_array($decoded) ? $decoded : [];
}

// Une issue de veille par dérive, pas une par lundi matin : tant que la
// précédente est ouverte, la dérive est déjà connue.
$open = gitlabApi('GET', $issuesUrl.'?state=opened&labels='.rawurlencode(ISSUE_LABEL), $token);

if ([] !== $open) {
    $existing = $open[0];
    $reference = is_array($existing) && isset($existing['web_url']) && is_string($existing['web_url']) ? $existing['web_url'] : 'inconnue';

    fwrite(STDOUT, "\nUne issue de veille est déjà ouverte : {$reference}. Aucune nouvelle issue.\n");

    exit(0);
}

$created = gitlabApi('POST', $issuesUrl, $token, [
    'title' => ISSUE_TITLE,
    'description' => $body,
    'labels' => ISSUE_LABEL,
]);

fwrite(STDOUT, sprintf(
    "\nIssue ouverte : %s\n",
    isset($created['web_url']) && is_string($created['web_url']) ? $created['web_url'] : 'créée',
));
