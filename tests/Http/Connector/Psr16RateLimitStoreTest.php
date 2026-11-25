<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http\Connector;

use Noneo\Sdk\Pennylane\Http\Connector\Psr16RateLimitStore;
use PHPUnit\Framework\TestCase;

final class Psr16RateLimitStoreTest extends TestCase
{
    public function testReplacesReservedCharactersOfTheLimiterName(): void
    {
        self::assertSame('pennylane.318b4e4f4e00.25_every_5', Psr16RateLimitStore::cle('pennylane:318b4e4f4e00:25_every_5'));
    }

    public function testCondensesAKeyLongerThanSixtyFourCharacters(): void
    {
        $cle = Psr16RateLimitStore::cle('pennylane:'.str_repeat('a', 80).':25_every_5');

        self::assertMatchesRegularExpression('/^[A-Za-z0-9_.]{1,64}$/', $cle);
    }

    public function testCondensedKeysStayDistinct(): void
    {
        self::assertNotSame(
            Psr16RateLimitStore::cle('pennylane:'.str_repeat('a', 80).':25_every_5'),
            Psr16RateLimitStore::cle('pennylane:'.str_repeat('b', 80).':25_every_5'),
        );
    }
}
