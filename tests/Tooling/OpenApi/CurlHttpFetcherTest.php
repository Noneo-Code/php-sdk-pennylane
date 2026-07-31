<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\CurlHttpFetcher;
use PHPUnit\Framework\TestCase;

final class CurlHttpFetcherTest extends TestCase
{
    public function testFetchesTheBodyOfAReachableUrl(): void
    {
        $fixture = tempnam(sys_get_temp_dir(), 'pennylane-fetch-');
        file_put_contents($fixture, 'contenu de la page');

        $fetcher = new CurlHttpFetcher(delayMicroseconds: 0);

        self::assertSame('contenu de la page', $fetcher->get('file://'.$fixture));

        unlink($fixture);
    }

    public function testThrowsWhenTheUrlCannotBeFetched(): void
    {
        $fetcher = new CurlHttpFetcher(delayMicroseconds: 0);

        $this->expectException(\RuntimeException::class);

        $fetcher->get('file:///chemin/inexistant/'.uniqid('pennylane-', true));
    }
}
