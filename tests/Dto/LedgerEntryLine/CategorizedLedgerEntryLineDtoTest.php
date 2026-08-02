<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategorizedLedgerEntryLineDto;
use PHPUnit\Framework\TestCase;

final class CategorizedLedgerEntryLineDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedResponsePayload(): void
    {
        $dto = CategorizedLedgerEntryLineDto::fromArray([
            'id' => 2,
            'label' => 'Employee - remuneration and contributions',
            'categories' => [
                [
                    'id' => 421,
                    'label' => 'HR - Salaries',
                    'weight' => '0.25',
                    'category_group' => ['id' => 229],
                    'analytical_code' => 'CODE123',
                    'created_at' => '2023-08-30T10:08:08.146343Z',
                    'updated_at' => '2023-08-30T10:08:08.146343Z',
                ],
            ],
        ]);

        self::assertSame(2, $dto->id);
        self::assertSame('Employee - remuneration and contributions', $dto->label);
        self::assertCount(1, $dto->categories);
        self::assertSame(421, $dto->categories[0]->id);
    }
}
