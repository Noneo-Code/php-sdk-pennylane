<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\ResourceGraph;
use PHPUnit\Framework\TestCase;

final class ResourceGraphTest extends TestCase
{
    public function testCollectsTheRequestsImportedByEveryReachableResource(): void
    {
        $dir = self::makeSrc([
            'Pennylane.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Resource\Journals;
                PHP,
            'Resource/Journals.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
                use Noneo\Sdk\Pennylane\Requests\Journals\GetJournal;
                PHP,
        ]);

        $reachable = ResourceGraph::requestsReachableFrom($dir, 'Pennylane');

        self::deleteDirectory($dir);

        self::assertSame(['GetJournal', 'GetJournals'], $reachable);
    }

    public function testFollowsResourcesNestedBehindOtherResources(): void
    {
        $dir = self::makeSrc([
            'PennylaneFirm.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Resource\Firm\Companies;
                PHP,
            'Resource/Firm/Companies.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Resource\Firm\Journals;
                PHP,
            'Resource/Firm/Journals.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
                PHP,
        ]);

        $reachable = ResourceGraph::requestsReachableFrom($dir, 'PennylaneFirm');

        self::deleteDirectory($dir);

        self::assertSame(['GetJournals'], $reachable);
    }

    public function testIgnoresRequestsWhoseOnlyResourceIsNeverExposed(): void
    {
        $dir = self::makeSrc([
            'CompanyScope.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Resource\Firm\Journals;
                PHP,
            'Resource/Firm/Journals.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
                PHP,
            'Resource/SupplierInvoices.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoices;
                PHP,
        ]);

        $reachable = ResourceGraph::requestsReachableFrom($dir, 'CompanyScope');

        self::deleteDirectory($dir);

        self::assertSame(['GetJournals'], $reachable);
    }

    public function testStopsAtTheDeclaredBarriers(): void
    {
        $dir = self::makeSrc([
            'PennylaneFirm.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\CompanyScope;
                use Noneo\Sdk\Pennylane\Resource\Firm\Companies;
                PHP,
            'CompanyScope.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Resource\Firm\Journals;
                PHP,
            'Resource/Firm/Companies.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Requests\Firm\Companies\Companies as ListCompanies;
                PHP,
            'Resource/Firm/Journals.php' => <<<'PHP'
                <?php
                use Noneo\Sdk\Pennylane\Requests\Journals\GetJournals;
                PHP,
        ]);

        $reachable = ResourceGraph::requestsReachableFrom($dir, 'PennylaneFirm', ['CompanyScope']);

        self::deleteDirectory($dir);

        self::assertSame(['Companies'], $reachable);
    }

    /** @param array<string, string> $files */
    private static function makeSrc(array $files): string
    {
        $dir = sys_get_temp_dir().'/pennylane-resource-graph-'.uniqid();

        foreach ($files as $relativePath => $source) {
            $target = $dir.'/'.$relativePath;
            $directory = dirname($target);

            if (!is_dir($directory)) {
                mkdir($directory, 0o777, true);
            }

            file_put_contents($target, $source);
        }

        return $dir;
    }

    private static function deleteDirectory(string $dir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
