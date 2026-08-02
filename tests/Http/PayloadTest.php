<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http;

use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use PHPUnit\Framework\TestCase;

final class PayloadTest extends TestCase
{
    public function testOmitsFieldsThatWereNotProvided(): void
    {
        self::assertSame(['name' => 'ACME'], Payload::build([
            'name' => 'ACME',
            'notes' => null,
        ]));
    }

    public function testSendsAnExplicitNullForAClearedField(): void
    {
        self::assertSame(['notes' => null], Payload::build([
            'notes' => Clear::value(),
        ]));
    }

    public function testKeepsFalseAndZeroWhichAreLegitimateValues(): void
    {
        self::assertSame(['draft' => false, 'rank' => 0], Payload::build([
            'draft' => false,
            'rank' => 0,
        ]));
    }

    public function testUnwrapsBackedEnums(): void
    {
        self::assertSame(['payment_conditions' => '30_days'], Payload::build([
            'payment_conditions' => PaymentConditions::ThirtyDays,
        ]));
    }

    public function testUnwrapsObjectsThatKnowHowToSerialiseThemselves(): void
    {
        $serialisable = new class {
            /** @return array<string, mixed> */
            public function toArray(): array
            {
                return ['line_1' => '2 rue de la Paix'];
            }
        };

        self::assertSame(
            ['billing_address' => ['line_1' => '2 rue de la Paix']],
            Payload::build(['billing_address' => $serialisable]),
        );
    }

    public function testClearIsASingleton(): void
    {
        self::assertSame(Clear::value(), Clear::value());
    }
}
