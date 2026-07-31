<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\LlmsIndexParser;
use PHPUnit\Framework\TestCase;

final class LlmsIndexParserTest extends TestCase
{
    public function testExtractsReferencePageUrlsFromLlmsTxt(): void
    {
        $llmsTxt = <<<'MD'
        # Company API Documentation

        ## Guides
        - [API Overview](https://pennylane.readme.io/docs/api-overview.md)

        ## API Reference
        - [List journals](https://pennylane.readme.io/reference/getjournals.md): List journals.
        - [Create a journal](https://pennylane.readme.io/reference/postjournals.md): Create a journal.
        MD;

        $urls = LlmsIndexParser::extractReferenceUrls($llmsTxt);

        self::assertSame(
            [
                'https://pennylane.readme.io/reference/getjournals.md',
                'https://pennylane.readme.io/reference/postjournals.md',
            ],
            $urls,
        );
    }

    public function testDeduplicatesRepeatedUrls(): void
    {
        $llmsTxt = <<<'MD'
        - [List journals](https://pennylane.readme.io/reference/getjournals.md): a
        - [List journals again](https://pennylane.readme.io/reference/getjournals.md): b
        MD;

        $urls = LlmsIndexParser::extractReferenceUrls($llmsTxt);

        self::assertSame(['https://pennylane.readme.io/reference/getjournals.md'], $urls);
    }

    public function testIgnoresNonReferenceLinks(): void
    {
        $llmsTxt = <<<'MD'
        - [Guide](https://pennylane.readme.io/docs/getting-started.md)
        MD;

        self::assertSame([], LlmsIndexParser::extractReferenceUrls($llmsTxt));
    }
}
