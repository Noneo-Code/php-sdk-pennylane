<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

final class RequestClassLocator
{
    /**
     * @return list<string>
     */
    public static function classBasenames(string $requestsDir): array
    {
        if (!is_dir($requestsDir)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($requestsDir, \FilesystemIterator::SKIP_DOTS),
        );

        $basenames = [];

        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);

            if ($file->isFile() && 'php' === $file->getExtension()) {
                $basenames[] = $file->getBasename('.php');
            }
        }

        sort($basenames);

        return $basenames;
    }
}
