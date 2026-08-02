<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Export;

use Noneo\Sdk\Pennylane\Dto\Export\ExportStatus;
use PHPUnit\Framework\TestCase;

final class ExportStatusTest extends TestCase
{
    public function testItExposesTheThreeDocumentedStatuses(): void
    {
        self::assertSame('pending', ExportStatus::Pending->value);
        self::assertSame('ready', ExportStatus::Ready->value);
        self::assertSame('error', ExportStatus::Error->value);
    }
}
