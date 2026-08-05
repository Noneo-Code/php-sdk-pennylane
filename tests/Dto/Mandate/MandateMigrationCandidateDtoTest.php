<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Mandate;

use Noneo\Sdk\Pennylane\Dto\Mandate\DirectDebitMethod;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateMigrationCandidateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateMigrationCandidateStatus;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateType;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class MandateMigrationCandidateDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getProAccountMandateMigrations', pointer: 'items')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'status' => 'available',
            'direct_debit_method' => 'GoCardless',
            'signed_at' => '2023-08-07T14:23:12.000Z',
            'error_message' => null,
            'migrated_at' => null,
            'migration_started_at' => null,
            'mandate' => ['id' => 42, 'type' => 'Mandate'],
            'customer' => [
                'id' => 42,
                'url' => 'https://app.pennylane.com/api/external/v2/customers/42',
                'pro_account_mandate' => ['url' => 'https://app.pennylane.com/api/external/v2/pro_account/mandates?filter=...'],
            ],
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = MandateMigrationCandidateDto::fromArray($this->documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame(MandateMigrationCandidateStatus::Available, $dto->status);
        self::assertSame(DirectDebitMethod::GoCardless, $dto->directDebitMethod);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->signedAt);
        self::assertNull($dto->errorMessage);
        self::assertNull($dto->migratedAt);
        self::assertNull($dto->migrationStartedAt);
        self::assertSame(42, $dto->mandateId);
        self::assertSame(MandateType::Mandate, $dto->mandateType);
        self::assertSame(42, $dto->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $dto->customerUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/pro_account/mandates?filter=...', $dto->proAccountMandateUrl);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->createdAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->updatedAt);
    }

    public function testFromArrayAllowsANullCustomer(): void
    {
        $payload = $this->documentedPayload();
        $payload['customer'] = null;

        $dto = MandateMigrationCandidateDto::fromArray($payload);

        self::assertNull($dto->customerId);
        self::assertNull($dto->customerUrl);
        self::assertNull($dto->proAccountMandateUrl);
    }
}
