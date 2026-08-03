<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\HttpFetcher;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\SpecDownloader;
use PHPUnit\Framework\TestCase;

final class SpecDownloaderTest extends TestCase
{
    public function testDownloadsAndDecodesTheSpec(): void
    {
        $downloader = new SpecDownloader(self::fetcherReturning('{"openapi":"3.0.1","paths":{"/api/external/v2/journals":{"get":{"operationId":"getJournals"}}}}'));

        $spec = $downloader->download('https://pennylane.readme.io/openapi/accounting.json');

        self::assertSame('3.0.1', $spec['openapi']);
        self::assertIsArray($spec['paths']);
        self::assertArrayHasKey('/api/external/v2/journals', $spec['paths']);
    }

    /**
     * Une page d'erreur HTML servie en 200 par le CDN ne doit pas remplacer la
     * spec committée par du vide.
     */
    public function testRejectsAResponseThatIsNotJson(): void
    {
        $downloader = new SpecDownloader(self::fetcherReturning('<html><body>503</body></html>'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Réponse non JSON pour https://example.test/spec.json');

        $downloader->download('https://example.test/spec.json');
    }

    public function testRejectsAJsonDocumentWithoutOperations(): void
    {
        $downloader = new SpecDownloader(self::fetcherReturning('{"openapi":"3.0.1","paths":{}}'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Spec sans opération pour https://example.test/spec.json');

        $downloader->download('https://example.test/spec.json');
    }

    private static function fetcherReturning(string $body): HttpFetcher
    {
        return new class($body) implements HttpFetcher {
            public function __construct(private readonly string $body)
            {
            }

            public function get(string $url): string
            {
                return $this->body;
            }
        };
    }
}
