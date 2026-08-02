<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\RequestClassLocator;
use PHPUnit\Framework\TestCase;

final class RequestClassLocatorTest extends TestCase
{
    public function testReadsTheMethodAndEndpointOfEachRequestClass(): void
    {
        $dir = self::makeRequests([
            'Journals/GetJournals.php' => <<<'PHP'
                <?php
                final class GetJournals extends Request
                {
                    protected Method $method = Method::GET;

                    public function resolveEndpoint(): string
                    {
                        return '/journals';
                    }
                }
                PHP,
        ]);

        $signatures = RequestClassLocator::signatures($dir);

        self::deleteDirectory($dir);

        self::assertCount(1, $signatures);
        self::assertSame('GetJournals', $signatures[0]->basename);
        self::assertSame('GET', $signatures[0]->method);
        self::assertSame('/journals', $signatures[0]->endpoint);
    }

    public function testNormalisesInterpolatedPathParameters(): void
    {
        $dir = self::makeRequests([
            'SupplierInvoices/PutSupplierInvoiceCategories.php' => <<<'PHP'
                <?php
                final class PutSupplierInvoiceCategories extends Request
                {
                    protected Method $method = Method::PUT;

                    public function resolveEndpoint(): string
                    {
                        return "/supplier_invoices/{$this->id}/categories";
                    }
                }
                PHP,
        ]);

        $signatures = RequestClassLocator::signatures($dir);

        self::deleteDirectory($dir);

        self::assertSame('PUT', $signatures[0]->method);
        self::assertSame('/supplier_invoices/{}/categories', $signatures[0]->endpoint);
    }

    public function testReturnsEmptyArrayWhenDirectoryDoesNotExist(): void
    {
        self::assertSame([], RequestClassLocator::signatures(sys_get_temp_dir().'/pennylane-does-not-exist-'.uniqid()));
    }

    /**
     * @param array<string, string> $files
     */
    private static function makeRequests(array $files): string
    {
        $dir = sys_get_temp_dir().'/pennylane-requests-'.uniqid();

        foreach ($files as $relativePath => $contents) {
            $path = $dir.'/'.$relativePath;
            mkdir(dirname($path), recursive: true);
            file_put_contents($path, $contents);
        }

        return $dir;
    }

    private static function deleteDirectory(string $dir): void
    {
        foreach (glob($dir.'/*/*.php') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($dir.'/*') ?: [] as $subdir) {
            rmdir($subdir);
        }
        rmdir($dir);
    }
}
