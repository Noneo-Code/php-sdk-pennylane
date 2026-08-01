<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\FiscalYear;

use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearStatus;
use PHPUnit\Framework\TestCase;

final class FiscalYearStatusTest extends TestCase
{
    public function testItExposesTheFourDocumentedStatuses(): void
    {
        self::assertSame('open', FiscalYearStatus::Open->value);
        self::assertSame('reopen', FiscalYearStatus::Reopen->value);
        self::assertSame('closed', FiscalYearStatus::Closed->value);
        self::assertSame('frozen', FiscalYearStatus::Frozen->value);
    }
}
