<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\ImputationDatesDto;
use PHPUnit\Framework\TestCase;

final class ImputationDatesDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $dto = ImputationDatesDto::fromArray(['start_date' => '2023-01-01', 'end_date' => '2023-01-31']);

        self::assertSame('2023-01-01', $dto->startDate);
        self::assertSame('2023-01-31', $dto->endDate);
    }

    public function testItAllowsNullDates(): void
    {
        $dto = ImputationDatesDto::fromArray(['start_date' => null, 'end_date' => null]);

        self::assertNull($dto->startDate);
        self::assertNull($dto->endDate);
    }
}
