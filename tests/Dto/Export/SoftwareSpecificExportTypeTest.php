<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Export;

use Noneo\Sdk\Pennylane\Dto\Export\SoftwareSpecificExportType;
use PHPUnit\Framework\TestCase;

final class SoftwareSpecificExportTypeTest extends TestCase
{
    public function testItExposesTheTwoDocumentedSoftwares(): void
    {
        self::assertSame('acd', SoftwareSpecificExportType::Acd->value);
        self::assertSame('agiris', SoftwareSpecificExportType::Agiris->value);
    }
}
