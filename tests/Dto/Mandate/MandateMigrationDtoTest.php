<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Mandate;

use Noneo\Sdk\Pennylane\Dto\Mandate\DirectDebitMethod;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateMigrationDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateType;
use PHPUnit\Framework\TestCase;

final class MandateMigrationDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'status' => 'pending',
            'direct_debit_method' => 'GoCardless',
            'signed_at' => '2023-08-07T14:23:12.000Z',
            'error_message' => null,
            'migrated_at' => null,
            'migration_started_at' => null,
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
            'mandate' => ['id' => 42, 'type' => 'SepaMandate'],
            'customer' => [
                'id' => 42,
                'url' => 'https://app.pennylane.com/api/external/v2/customers/42',
                'pro_account_mandate' => ['url' => 'https://app.pennylane.com/api/external/v2/pro_account/mandates?filter=...'],
            ],
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = MandateMigrationDto::fromArray($this->documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('pending', $dto->status);
        self::assertSame(DirectDebitMethod::GoCardless, $dto->directDebitMethod);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->signedAt);
        self::assertNull($dto->errorMessage);
        self::assertNull($dto->migratedAt);
        self::assertNull($dto->migrationStartedAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->createdAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->updatedAt);
        self::assertSame(42, $dto->mandateId);
        self::assertSame(MandateType::SepaMandate, $dto->mandateType);
        self::assertSame(42, $dto->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $dto->customerUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/pro_account/mandates?filter=...', $dto->proAccountMandateUrl);
    }

    public function testFromArrayAllowsANullDirectDebitMethod(): void
    {
        $payload = $this->documentedPayload();
        $payload['direct_debit_method'] = null;

        $dto = MandateMigrationDto::fromArray($payload);

        self::assertNull($dto->directDebitMethod);
    }
}
