<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\BankEstablishment;

use Noneo\Sdk\Pennylane\Dto\BankEstablishment\BankEstablishmentDto;
use PHPUnit\Framework\TestCase;

final class BankEstablishmentDtoTest extends TestCase
{
    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = BankEstablishmentDto::fromArray([
            'id' => 1,
            'name' => 'Bank 1',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(1, $dto->id);
        self::assertSame('Bank 1', $dto->name);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }
}
