<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Me\MeDto;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Tests\Dto\Me\MeDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class MeTest extends TestCase
{
    public function testGetReturnsAMeDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make(MeDtoTest::documentedPayload())]));

        $me = $pennylane->me()->get();

        self::assertInstanceOf(MeDto::class, $me);
        self::assertSame(123456, $me->company->id);
    }
}
