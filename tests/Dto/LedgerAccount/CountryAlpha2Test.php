<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerAccount;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use PHPUnit\Framework\TestCase;

final class CountryAlpha2Test extends TestCase
{
    public function testItExposesAllTheCountriesDocumentedByThePostLedgerAccountsEndpoint(): void
    {
        self::assertCount(35, CountryAlpha2::cases());
    }

    public function testItParsesFranceAndTheWildcard(): void
    {
        self::assertSame('FR', CountryAlpha2::FR->value);
        self::assertSame('any', CountryAlpha2::Any->value);
    }

    public function testItParsesNorwayDespiteItsYamlAmbiguousCode(): void
    {
        self::assertSame('NO', CountryAlpha2::NO->value);
    }
}
