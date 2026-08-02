<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Export;

use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use PHPUnit\Framework\TestCase;

final class AnalyticalGeneralLedgerExportModeTest extends TestCase
{
    public function testItExposesTheTwoDocumentedModes(): void
    {
        self::assertSame('in_line', AnalyticalGeneralLedgerExportMode::InLine->value);
        self::assertSame('in_column', AnalyticalGeneralLedgerExportMode::InColumn->value);
    }
}
