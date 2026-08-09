<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Mandate;

use Noneo\Sdk\Pennylane\Dto\Mandate\ProAccountMandateDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class ProAccountMandateDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getProAccountMandates', pointer: 'items')]
    public static function documentedPayload(): array
    {
        return [
            'status' => 'enabled',
            'early_execution_date_permitted' => false,
            'active_billing_subscription' => false,
            'signed_at' => '2024-01-15',
            'created_at' => '2024-01-15T10:30:00Z',
            'pdf_url' => 'https://example.com/mandate.pdf',
            'customer' => ['id' => 47334785, 'url' => 'https://api.example.com/api/external/v2/customers/47334785'],
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = ProAccountMandateDto::fromArray($this->documentedPayload());

        self::assertSame('enabled', $dto->status);
        self::assertFalse($dto->earlyExecutionDatePermitted);
        self::assertFalse($dto->activeBillingSubscription);
        self::assertSame('2024-01-15', $dto->signedAt);
        self::assertSame('2024-01-15T10:30:00Z', $dto->createdAt);
        self::assertSame('https://example.com/mandate.pdf', $dto->pdfUrl);
        self::assertSame(47334785, $dto->customerId);
        self::assertSame('https://api.example.com/api/external/v2/customers/47334785', $dto->customerUrl);
    }

    public function testFromArrayAllowsNullableFields(): void
    {
        $payload = $this->documentedPayload();
        $payload['signed_at'] = null;
        $payload['pdf_url'] = null;
        $payload['customer'] = null;

        $dto = ProAccountMandateDto::fromArray($payload);

        self::assertNull($dto->signedAt);
        self::assertNull($dto->pdfUrl);
        self::assertNull($dto->customerId);
        self::assertNull($dto->customerUrl);
    }
}
