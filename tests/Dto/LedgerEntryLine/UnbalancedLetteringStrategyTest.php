<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\UnbalancedLetteringStrategy;
use PHPUnit\Framework\TestCase;

final class UnbalancedLetteringStrategyTest extends TestCase
{
    public function testItExposesTheTwoDocumentedStrategies(): void
    {
        self::assertSame('none', UnbalancedLetteringStrategy::None->value);
        self::assertSame('partial', UnbalancedLetteringStrategy::Partial->value);
    }
}
