<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Mandate;

use Noneo\Sdk\Pennylane\Dto\Mandate\GocardlessMandateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\GocardlessMandateStatus;
use PHPUnit\Framework\TestCase;

final class GocardlessMandateDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'external_reference' => 'MD000VDD0HN1RP',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'status' => 'active',
            'external_customer_account' => 'test_account_id',
            'external_customer_label' => 'test_label',
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = GocardlessMandateDto::fromArray($this->documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('MD000VDD0HN1RP', $dto->externalReference);
        self::assertSame(42, $dto->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $dto->customerUrl);
        self::assertSame(GocardlessMandateStatus::Active, $dto->status);
        self::assertSame('test_account_id', $dto->externalCustomerAccount);
        self::assertSame('test_label', $dto->externalCustomerLabel);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->createdAt);
        self::assertSame('2023-08-07T14:23:12.000Z', $dto->updatedAt);
    }

    public function testFromArrayAllowsANullCustomer(): void
    {
        $payload = $this->documentedPayload();
        $payload['customer'] = null;

        $dto = GocardlessMandateDto::fromArray($payload);

        self::assertNull($dto->customerId);
        self::assertNull($dto->customerUrl);
    }

    public function testFromArrayAllowsNullExternalReferenceAndLabel(): void
    {
        $payload = $this->documentedPayload();
        $payload['external_reference'] = null;
        $payload['external_customer_label'] = null;

        $dto = GocardlessMandateDto::fromArray($payload);

        self::assertNull($dto->externalReference);
        self::assertNull($dto->externalCustomerLabel);
    }
}
