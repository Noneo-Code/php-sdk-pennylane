<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Me;

use Noneo\Sdk\Pennylane\Dto\Me\AccountingLogic;
use Noneo\Sdk\Pennylane\Dto\Me\Locale;
use Noneo\Sdk\Pennylane\Dto\Me\MeDto;
use PHPUnit\Framework\TestCase;

final class MeDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    public static function documentedPayload(): array
    {
        return [
            'user' => [
                'id' => 12345,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'jdoe@pennylane.com',
                'locale' => 'fr',
            ],
            'company' => [
                'id' => 123456,
                'name' => 'Pennylane',
                'reg_no' => '123456789',
                'accounting_logic' => 'FR_PCG',
            ],
            'scopes' => ['customer_invoices', 'suppliers'],
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $me = MeDto::fromArray(self::documentedPayload());

        self::assertNotNull($me->user);
        self::assertSame(12345, $me->user->id);
        self::assertSame('John', $me->user->firstName);
        self::assertSame('Doe', $me->user->lastName);
        self::assertSame('jdoe@pennylane.com', $me->user->email);
        self::assertSame(Locale::Fr, $me->user->locale);
        self::assertSame(123456, $me->company->id);
        self::assertSame('Pennylane', $me->company->name);
        self::assertSame('123456789', $me->company->regNo);
        self::assertSame(AccountingLogic::FrPcg, $me->company->accountingLogic);
        self::assertSame(['customer_invoices', 'suppliers'], $me->scopes);
    }

    public function testItHandlesANullUser(): void
    {
        $payload = self::documentedPayload();
        $payload['user'] = null;

        $me = MeDto::fromArray($payload);

        self::assertNull($me->user);
    }

    public function testItRejectsAMissingCompany(): void
    {
        $payload = self::documentedPayload();
        unset($payload['company']);

        $this->expectException(\InvalidArgumentException::class);

        MeDto::fromArray($payload);
    }
}
