<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\FiscalYear;

use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearDto;
use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearStatus;
use PHPUnit\Framework\TestCase;

final class FiscalYearDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedFiscalYearPayload(): void
    {
        $dto = FiscalYearDto::fromArray([
            'id' => 12,
            'start' => '2023-08-30',
            'finish' => '2022-12-31',
            'status' => 'open',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(12, $dto->id);
        self::assertSame('2023-08-30', $dto->start);
        self::assertSame('2022-12-31', $dto->finish);
        self::assertSame(FiscalYearStatus::Open, $dto->status);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }

    public function testItBuildsFromTheDocumentedFirmFiscalYearPayloadWithoutTimestamps(): void
    {
        $dto = FiscalYearDto::fromArray([
            'id' => 12,
            'start' => '2023-08-30',
            'finish' => '2022-12-31',
            'status' => 'open',
        ]);

        self::assertNull($dto->createdAt);
        self::assertNull($dto->updatedAt);
    }
}
