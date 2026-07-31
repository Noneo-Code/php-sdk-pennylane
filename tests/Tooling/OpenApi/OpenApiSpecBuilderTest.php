<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\HttpFetcher;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\OpenApiSpecBuilder;
use PHPUnit\Framework\TestCase;

final class OpenApiSpecBuilderTest extends TestCase
{
    public function testBuildsAMergedSpecAndReportsSkippedPages(): void
    {
        $pages = [
            'https://pennylane.readme.io/llms.txt' => <<<'MD'
                ## API Reference
                - [List journals](https://pennylane.readme.io/reference/getjournals.md): List journals.
                - [Create a journal](https://pennylane.readme.io/reference/postjournals.md): Create a journal.
                - [Scopes](https://pennylane.readme.io/reference/scopes.md)
                MD,
            'https://pennylane.readme.io/reference/getjournals.md' => <<<'MD'
                ```json
                {
                  "openapi": "3.0.1",
                  "info": {"title": "Company V2", "version": "2.0"},
                  "servers": [{"url": "https://app.pennylane.com"}],
                  "paths": {
                    "/api/external/v2/journals": {
                      "get": {"operationId": "getJournals"}
                    }
                  }
                }
                ```
                MD,
            'https://pennylane.readme.io/reference/postjournals.md' => <<<'MD'
                ```json
                {
                  "openapi": "3.0.1",
                  "info": {"title": "Company V2", "version": "2.0"},
                  "servers": [{"url": "https://app.pennylane.com"}],
                  "paths": {
                    "/api/external/v2/journals": {
                      "post": {"operationId": "postJournals"}
                    }
                  }
                }
                ```
                MD,
            'https://pennylane.readme.io/reference/scopes.md' => <<<'MD'
                # Scopes

                Pas de fragment OpenAPI sur cette page.
                MD,
        ];

        $fetcher = new class($pages) implements HttpFetcher {
            /**
             * @param array<string, string> $pages
             */
            public function __construct(private readonly array $pages)
            {
            }

            public function get(string $url): string
            {
                return $this->pages[$url];
            }
        };

        $builder = new OpenApiSpecBuilder($fetcher);
        $result = $builder->build('https://pennylane.readme.io/llms.txt');

        /** @var array<string, array<string, mixed>> $paths */
        $paths = $result->spec['paths'];

        self::assertSame(
            ['get', 'post'],
            array_keys($paths['/api/external/v2/journals']),
        );
        self::assertSame(['https://pennylane.readme.io/reference/scopes.md'], $result->skippedUrls);
    }
}
