<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\PaRegistration\PaRegistrationDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Tests\Dto\PaRegistration\PaRegistrationDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PaRegistrationsTest extends TestCase
{
    public function testListReturnsACursorPageOfPaRegistrationDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [PaRegistrationDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->paRegistrations()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(PaRegistrationDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }
}
