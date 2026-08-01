<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerAccount;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use PHPUnit\Framework\TestCase;

final class VatRateTest extends TestCase
{
    public function testItExposesAllTheRatesDocumentedByThePostLedgerAccountsEndpoint(): void
    {
        self::assertCount(149, VatRate::cases());
    }

    public function testItParsesAFrenchStandardRate(): void
    {
        self::assertSame('FR_200', VatRate::FR_200->value);
    }

    public function testItParsesAConstructionSpecificRate(): void
    {
        self::assertSame('FR_85_construction', VatRate::Fr85Construction->value);
    }

    public function testItParsesTheIntracomAndWildcardRates(): void
    {
        self::assertSame('intracom_21', VatRate::Intracom21->value);
        self::assertSame('crossborder', VatRate::Crossborder->value);
        self::assertSame('extracom', VatRate::Extracom->value);
        self::assertSame('exempt', VatRate::Exempt->value);
        self::assertSame('any', VatRate::Any->value);
    }
}
