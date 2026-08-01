<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\ContactDto;
use PHPUnit\Framework\TestCase;

final class ContactDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedGetCustomerContactsPayload(): void
    {
        $dto = ContactDto::fromArray([
            'id' => 421,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'role' => 'accountant',
            'email' => 'jane.doe@example.com',
            'telephone_number' => '+33612345678',
            'mobile_number' => '+33698765432',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(421, $dto->id);
        self::assertSame('Jane', $dto->firstName);
        self::assertSame('Doe', $dto->lastName);
        self::assertSame('accountant', $dto->role);
        self::assertSame('jane.doe@example.com', $dto->email);
        self::assertSame('+33612345678', $dto->telephoneNumber);
        self::assertSame('+33698765432', $dto->mobileNumber);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }
}
