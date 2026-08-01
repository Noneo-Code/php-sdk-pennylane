<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Supplier;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDto;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDueDateRule;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierPaymentMethod;
use PHPUnit\Framework\TestCase;

final class SupplierDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Pennylane',
            'establishment_no' => '82762938500014',
            'reg_no' => '827629385',
            'vat_number' => 'FR32123456789',
            'ledger_account' => ['id' => 1],
            'emails' => ['hello@example.org'],
            'iban' => 'FRXXXXXXXXXXXXXXXXXXXXXXXXX',
            'postal_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            'supplier_payment_method' => 'automatic_transfer',
            'supplier_due_date_delay' => 30,
            'supplier_due_date_rule' => 'days',
            'external_reference' => 'FR123',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testFromArrayBuildsASupplierDtoFromTheDocumentedPayload(): void
    {
        $supplier = SupplierDto::fromArray($this->documentedPayload());

        self::assertSame(42, $supplier->id);
        self::assertSame('Pennylane', $supplier->name);
        self::assertSame('82762938500014', $supplier->establishmentNo);
        self::assertSame('827629385', $supplier->regNo);
        self::assertSame('FR32123456789', $supplier->vatNumber);
        self::assertSame(1, $supplier->ledgerAccountId);
        self::assertSame(['hello@example.org'], $supplier->emails);
        self::assertSame('FRXXXXXXXXXXXXXXXXXXXXXXXXX', $supplier->iban);
        self::assertEquals(new Address('8 rue de la paix', '75002', 'Paris', 'FR'), $supplier->postalAddress);
        self::assertSame(SupplierPaymentMethod::AutomaticTransfer, $supplier->supplierPaymentMethod);
        self::assertSame(30, $supplier->supplierDueDateDelay);
        self::assertSame(SupplierDueDateRule::Days, $supplier->supplierDueDateRule);
        self::assertSame('FR123', $supplier->externalReference);
        self::assertSame('2023-08-30T10:08:08.146343Z', $supplier->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $supplier->updatedAt);
    }

    public function testFromArrayAllowsNullableFieldsToBeNull(): void
    {
        $payload = $this->documentedPayload();
        $payload['establishment_no'] = null;
        $payload['reg_no'] = null;
        $payload['ledger_account'] = null;
        $payload['supplier_payment_method'] = null;
        $payload['supplier_due_date_delay'] = null;
        $payload['supplier_due_date_rule'] = null;

        $supplier = SupplierDto::fromArray($payload);

        self::assertNull($supplier->establishmentNo);
        self::assertNull($supplier->regNo);
        self::assertNull($supplier->ledgerAccountId);
        self::assertNull($supplier->supplierPaymentMethod);
        self::assertNull($supplier->supplierDueDateDelay);
        self::assertNull($supplier->supplierDueDateRule);
    }
}
