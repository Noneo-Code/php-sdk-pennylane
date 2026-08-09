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

    /**
     * Un float ne représente pas exactement un montant décimal : `json_encode`
     * peut écrire `120.5` là où l'API attend `"120.50"`, et une écriture
     * comptable fausse d'un centime ne se voit pas. On refuse à la source.
     */
    public function testRefusesAFloatAndNamesTheOffendingField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount');

        Payload::build(['amount' => 1.1]);
    }

    public function testRefusesAFloatNestedInAnArrayOfInvoiceLines(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invoice_lines[0][raw_currency_unit_price]');

        Payload::build([
            'invoice_lines' => [
                ['label' => 'Prestation', 'raw_currency_unit_price' => 120.50],
            ],
        ]);
    }

    public function testRefusesAFloatReturnedByAnInputDto(): void
    {
        $line = new class {
            /** @return array<string, mixed> */
            public function toArray(): array
            {
                return ['raw_currency_unit_price' => 120.50];
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('line[raw_currency_unit_price]');

        Payload::build(['line' => $line]);
    }

    /**
     * Aucune exception pour les floats « entiers » : `2.0` est un float comme
     * un autre, et une règle sans exception est la seule qui s'explique.
     */
    public function testRefusesAWholeFloatToo(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Payload::build(['quantity' => 2.0]);
    }

    public function testSuggestsTheDecimalStringToWriteInstead(): void
    {
        $this->expectExceptionMessage("'120.50'");

        Payload::build(['amount' => 120.50]);
    }

    public function testLetsDecimalStringsIntegersAndClearThrough(): void
    {
        self::assertSame(
            ['amount' => '120.50', 'quantity' => 3, 'notes' => null],
            Payload::build([
                'amount' => '120.50',
                'quantity' => 3,
                'notes' => Clear::value(),
            ]),
        );
    }
}
