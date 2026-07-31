<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperationsReader;
use PHPUnit\Framework\TestCase;

final class SpecOperationsReaderTest extends TestCase
{
    public function testReadsOperationsFromAYamlSpecFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pennylane-spec-').'.yaml';
        file_put_contents($path, <<<'YAML'
            openapi: 3.0.1
            paths:
                /api/external/v2/journals:
                    get:
                        operationId: getJournals
                    post:
                        operationId: postJournals
                /api/external/v2/suppliers:
                    get:
                        operationId: getSuppliers
            YAML);

        $operations = SpecOperationsReader::fromYamlFile($path);

        unlink($path);

        self::assertCount(3, $operations);
        self::assertSame('GET', $operations[0]->method);
        self::assertSame('/api/external/v2/journals', $operations[0]->path);
        self::assertSame('getJournals', $operations[0]->operationId);
    }
}
