<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\PaRegistration;

use Noneo\Sdk\Pennylane\Dto\PaRegistration\ExchangeDirection;
use Noneo\Sdk\Pennylane\Dto\PaRegistration\PaRegistrationDto;
use Noneo\Sdk\Pennylane\Dto\PaRegistration\PaRegistrationStatus;
use PHPUnit\Framework\TestCase;

final class PaRegistrationDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    public static function documentedPayload(): array
    {
        return [
            'id' => 1,
            'siret' => '98493148500010',
            'siren' => '993369859',
            'status' => 'activated',
            'exchange_direction' => 'emission_and_reception',
            'created_at' => '2026-02-23T10:53:00.000Z',
            'updated_at' => '2026-02-23T10:53:00.000Z',
        ];
    }

    public function testFromArrayBuildsDtoFromDocumentedPayload(): void
    {
        $dto = PaRegistrationDto::fromArray(self::documentedPayload());

        self::assertSame(1, $dto->id);
        self::assertSame('98493148500010', $dto->siret);
        self::assertSame('993369859', $dto->siren);
        self::assertSame(PaRegistrationStatus::Activated, $dto->status);
        self::assertSame(ExchangeDirection::EmissionAndReception, $dto->exchangeDirection);
        self::assertSame('2026-02-23T10:53:00.000Z', $dto->createdAt);
        self::assertSame('2026-02-23T10:53:00.000Z', $dto->updatedAt);
    }

    public function testSiretIsNullForASirenLevelRegistration(): void
    {
        $payload = self::documentedPayload();
        $payload['siret'] = null;

        $dto = PaRegistrationDto::fromArray($payload);

        self::assertNull($dto->siret);
    }
}
