<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Auth;

use Noneo\Sdk\Pennylane\Auth\AccessToken;
use PHPUnit\Framework\TestCase;

final class AccessTokenTest extends TestCase
{
    public function testKnowsWhenItHasExpired(): void
    {
        $token = new AccessToken('t', expiresAt: new \DateTimeImmutable('2026-01-01 12:00:00'));

        self::assertTrue($token->hasExpired(new \DateTimeImmutable('2026-01-01 12:00:01')));
        self::assertFalse($token->hasExpired(new \DateTimeImmutable('2026-01-01 11:59:59')));
    }

    public function testNeverExpiresWithoutAnExpiry(): void
    {
        self::assertFalse((new AccessToken('t'))->hasExpired());
    }
}
