<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Mandate;

use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use PHPUnit\Framework\TestCase;

final class SepaMandateDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 12345,
            'bank' => 'BNP Paribas',
            'bic' => 'BNPAFRPP',
            'iban' => 'FR3612739000506556687647Z31',
            'sequence_type' => 'RCUR',
            'signed_at' => '2025-04-24',
            'identifier' => 'K-02-2023-12345',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = SepaMandateDto::fromArray($this->documentedPayload());

        self::assertSame(12345, $dto->id);
        self::assertSame('BNP Paribas', $dto->bank);
        self::assertSame('BNPAFRPP', $dto->bic);
        self::assertSame('FR3612739000506556687647Z31', $dto->iban);
        self::assertSame(SepaMandateSequenceType::Rcur, $dto->sequenceType);
        self::assertSame('2025-04-24', $dto->signedAt);
        self::assertSame('K-02-2023-12345', $dto->identifier);
        self::assertSame(42, $dto->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $dto->customerUrl);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->createdAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->updatedAt);
    }

    public function testFromArrayAllowsANullBank(): void
    {
        $payload = $this->documentedPayload();
        $payload['bank'] = null;

        $dto = SepaMandateDto::fromArray($payload);

        self::assertNull($dto->bank);
    }
}
