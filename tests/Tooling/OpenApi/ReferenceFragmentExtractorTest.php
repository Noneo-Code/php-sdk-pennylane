<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\ReferenceFragmentExtractor;
use PHPUnit\Framework\TestCase;

final class ReferenceFragmentExtractorTest extends TestCase
{
    public function testExtractsTheOpenapiJsonFragment(): void
    {
        $markdown = <<<'MD'
        # List journals

        List journals.

        # OpenAPI definition

        ```json
        {
          "openapi": "3.0.1",
          "paths": {
            "/api/external/v2/journals": {
              "get": {
                "operationId": "getJournals"
              }
            }
          }
        }
        ```
        MD;

        $fragment = ReferenceFragmentExtractor::extract($markdown);
        self::assertNotNull($fragment);

        /** @var array<string, array<string, array<string, mixed>>> $paths */
        $paths = $fragment['paths'];

        self::assertSame('getJournals', $paths['/api/external/v2/journals']['get']['operationId']);
    }

    public function testReturnsNullWhenNoJsonFragmentIsPresent(): void
    {
        $markdown = <<<'MD'
        # Scopes

        Scopes are used to limit a token's access to Pennylane data.
        MD;

        self::assertNull(ReferenceFragmentExtractor::extract($markdown));
    }

    public function testThrowsOnMalformedJsonFragment(): void
    {
        $markdown = <<<'MD'
        ```json
        { "openapi": "3.0.1",
        ```
        MD;

        $this->expectException(\JsonException::class);

        ReferenceFragmentExtractor::extract($markdown);
    }
}
