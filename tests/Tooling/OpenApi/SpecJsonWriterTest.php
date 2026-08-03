<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecJsonWriter;
use PHPUnit\Framework\TestCase;

final class SpecJsonWriterTest extends TestCase
{
    public function testIndentsTheSpecSoThatGitDiffsStayReadable(): void
    {
        $json = SpecJsonWriter::toJson(['openapi' => '3.0.1', 'paths' => ['/journals' => ['get' => ['operationId' => 'getJournals']]]]);

        self::assertSame(<<<'JSON'
            {
                "openapi": "3.0.1",
                "paths": {
                    "/journals": {
                        "get": {
                            "operationId": "getJournals"
                        }
                    }
                }
            }

            JSON, $json);
    }

    /**
     * Les chemins de l'API sont pleins de `/` et les descriptions pleines
     * d'accents : échappés, ils rendent le fichier illisible au `grep`.
     */
    public function testLeavesSlashesAndAccentsUnescaped(): void
    {
        $json = SpecJsonWriter::toJson(['paths' => ['/api/external/v2/journals' => ['summary' => 'Journaux comptables créés']]]);

        self::assertStringContainsString('"/api/external/v2/journals"', $json);
        self::assertStringContainsString('créés', $json);
    }

    public function testEndsWithASingleNewline(): void
    {
        self::assertStringEndsWith("}\n", SpecJsonWriter::toJson(['openapi' => '3.0.1']));
        self::assertStringEndsNotWith("}\n\n", SpecJsonWriter::toJson(['openapi' => '3.0.1']));
    }

    public function testRoundTripsTheSpecUnchanged(): void
    {
        $spec = ['openapi' => '3.0.1', 'paths' => ['/journals' => ['get' => ['operationId' => 'getJournals', 'deprecated' => false]]]];

        self::assertSame($spec, json_decode(SpecJsonWriter::toJson($spec), true));
    }
}
