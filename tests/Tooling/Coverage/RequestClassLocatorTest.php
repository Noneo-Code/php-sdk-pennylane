<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Coverage;

use Noneo\Sdk\Pennylane\Tooling\Coverage\RequestClassLocator;
use PHPUnit\Framework\TestCase;

final class RequestClassLocatorTest extends TestCase
{
    public function testReturnsClassBasenamesFoundRecursively(): void
    {
        $dir = sys_get_temp_dir().'/pennylane-requests-'.uniqid();
        mkdir($dir.'/Journals', recursive: true);
        file_put_contents($dir.'/Journals/GetJournals.php', '<?php');
        file_put_contents($dir.'/Journals/PostJournals.php', '<?php');

        $basenames = RequestClassLocator::classBasenames($dir);

        self::deleteDirectory($dir);

        self::assertSame(['GetJournals', 'PostJournals'], $basenames);
    }

    public function testReturnsEmptyArrayWhenDirectoryDoesNotExist(): void
    {
        self::assertSame([], RequestClassLocator::classBasenames(sys_get_temp_dir().'/pennylane-does-not-exist-'.uniqid()));
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
