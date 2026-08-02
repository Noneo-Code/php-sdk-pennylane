<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Export;

use Noneo\Sdk\Pennylane\Dto\Export\AccountNumbersType;
use PHPUnit\Framework\TestCase;

final class AccountNumbersTypeTest extends TestCase
{
    public function testItExposesTheTwoDocumentedTypes(): void
    {
        self::assertSame('original', AccountNumbersType::Original->value);
        self::assertSame('pennylane', AccountNumbersType::Pennylane->value);
    }
}
