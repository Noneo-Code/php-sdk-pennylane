<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\BankEstablishment\BankEstablishmentDto;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class BankEstablishmentsTest extends TestCase
{
    public function testListReturnsACursorPageOfBankEstablishmentDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [
                ['id' => 1, 'name' => 'Bank 1', 'created_at' => '2023-08-30T10:08:08.146343Z', 'updated_at' => '2023-08-30T10:08:08.146343Z'],
            ],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->bankEstablishments()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(BankEstablishmentDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
        self::assertSame('Bank 1', $page->items[0]->name);
        self::assertFalse($page->hasMore);
    }
}
