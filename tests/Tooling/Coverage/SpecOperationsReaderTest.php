<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperationsReader;
use PHPUnit\Framework\TestCase;

final class SpecOperationsReaderTest extends TestCase
{
    public function testReadsOperationsFromAJsonSpecFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pennylane-spec-').'.json';
        file_put_contents($path, <<<'JSON'
            {
                "openapi": "3.0.1",
                "paths": {
                    "/api/external/v2/journals": {
                        "get": {"operationId": "getJournals"},
                        "post": {"operationId": "postJournals"}
                    },
                    "/api/external/v2/suppliers": {
                        "get": {"operationId": "getSuppliers"}
                    }
                }
            }
            JSON);

        $operations = SpecOperationsReader::fromJsonFile($path);

        unlink($path);

        self::assertCount(3, $operations);
        self::assertSame('GET', $operations[0]->method);
        self::assertSame('/api/external/v2/journals', $operations[0]->path);
        self::assertSame('getJournals', $operations[0]->operationId);
    }

    public function testIgnoresPathLevelKeysThatAreNotHttpMethods(): void
    {
        $operations = SpecOperationsReader::fromArray([
            'paths' => [
                '/api/external/v2/journals/{id}' => [
                    'parameters' => [['name' => 'id', 'in' => 'path']],
                    'get' => ['operationId' => 'getJournal'],
                ],
            ],
        ]);

        self::assertCount(1, $operations);
        self::assertSame('getJournal', $operations[0]->operationId);
    }

    public function testRejectsAnOperationWithoutOperationId(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('operationId manquant ou invalide pour get /api/external/v2/journals.');

        SpecOperationsReader::fromArray([
            'paths' => [
                '/api/external/v2/journals' => [
                    'get' => ['summary' => 'Liste les journaux'],
                ],
            ],
        ]);
    }

    public function testRejectsAFileThatIsNotValidJson(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pennylane-spec-').'.json';
        file_put_contents($path, 'openapi: 3.0.1');

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage("Spec JSON illisible : {$path}");

            SpecOperationsReader::fromJsonFile($path);
        } finally {
            unlink($path);
        }
    }
}
