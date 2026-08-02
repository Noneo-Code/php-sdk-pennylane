<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\OpenApiSpecBuildResult;
use PHPUnit\Framework\TestCase;

final class OpenApiSpecBuildResultTest extends TestCase
{
    public function testCountsEveryOperationAcrossPaths(): void
    {
        $result = new OpenApiSpecBuildResult(
            [
                'paths' => [
                    '/api/external/v2/journals' => [
                        'get' => ['operationId' => 'getJournals'],
                        'post' => ['operationId' => 'postJournals'],
                    ],
                    '/api/external/v2/journals/{id}' => [
                        'get' => ['operationId' => 'getJournal'],
                    ],
                ],
            ],
            skippedUrls: [],
        );

        self::assertSame(3, $result->operationCount());
    }

    public function testCountsZeroWhenTheSpecCarriesNoPath(): void
    {
        self::assertSame(0, (new OpenApiSpecBuildResult([], skippedUrls: []))->operationCount());
    }
}
