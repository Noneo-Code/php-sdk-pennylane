<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\YamlSpecWriter;
use PHPUnit\Framework\TestCase;

final class YamlSpecWriterTest extends TestCase
{
    public function testDumpsASpecAsReadableYaml(): void
    {
        $spec = [
            'openapi' => '3.0.1',
            'info' => ['title' => 'Company V2', 'version' => '2.0'],
            'paths' => [
                '/api/external/v2/journals' => [
                    'get' => ['operationId' => 'getJournals'],
                ],
            ],
        ];

        $yaml = YamlSpecWriter::toYaml($spec);

        self::assertSame(<<<'YAML'
            openapi: 3.0.1
            info:
                title: 'Company V2'
                version: '2.0'
            paths:
                /api/external/v2/journals:
                    get:
                        operationId: getJournals

            YAML, $yaml);
    }

    public function testDumpingTheSameSpecTwiceProducesTheSameOutput(): void
    {
        $spec = ['openapi' => '3.0.1', 'paths' => ['/a' => ['get' => ['operationId' => 'a']]]];

        self::assertSame(YamlSpecWriter::toYaml($spec), YamlSpecWriter::toYaml($spec));
    }
}
