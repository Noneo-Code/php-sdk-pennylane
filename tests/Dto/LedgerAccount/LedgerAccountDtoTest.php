<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerAccount;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\LedgerAccountDto;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use PHPUnit\Framework\TestCase;

final class LedgerAccountDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedLedgerAccountPayload(): void
    {
        $dto = LedgerAccountDto::fromArray([
            'id' => 124,
            'number' => '512',
            'label' => 'Secondary Account',
            'vat_rate' => 'any',
            'country_alpha2' => 'FR',
            'enabled' => true,
            'type' => 'custom',
            'letterable' => false,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(124, $dto->id);
        self::assertSame('512', $dto->number);
        self::assertSame('Secondary Account', $dto->label);
        self::assertSame(VatRate::Any, $dto->vatRate);
        self::assertSame(CountryAlpha2::FR, $dto->countryAlpha2);
        self::assertTrue($dto->enabled);
        self::assertSame('custom', $dto->type);
        self::assertFalse($dto->letterable);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }

    public function testItParsesASpecificFrenchVatRate(): void
    {
        $dto = LedgerAccountDto::fromArray([
            'id' => 124,
            'number' => '706000',
            'label' => 'Ventes',
            'vat_rate' => 'FR_200',
            'country_alpha2' => 'FR',
            'enabled' => true,
            'type' => 'custom',
            'letterable' => false,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(VatRate::FR_200, $dto->vatRate);
    }
}
