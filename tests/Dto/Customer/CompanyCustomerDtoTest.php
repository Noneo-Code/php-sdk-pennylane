<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\CompanyCustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerType;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class CompanyCustomerDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getCompanyCustomer')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'My Company',
            'billing_iban' => 'FR1420041010050500013M02606',
            'payment_conditions' => '30_days',
            'recipient' => 'John Doe',
            'phone' => '+33612345678',
            'reference' => 'REF-1234',
            'notes' => 'Some notes',
            'vat_number' => 'FR12345678901',
            'reg_no' => '123456789',
            'ledger_account' => ['id' => 17],
            'emails' => ['hello@example.org'],
            'billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            'delivery_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'external_reference' => '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
            'billing_language' => 'fr_FR',
            'mandates' => ['url' => 'https://app.pennylane.com/api/external/v2/gocardless_mandates'],
            'pro_account_mandates' => ['url' => 'https://app.pennylane.com/api/external/v2/pro_account/mandates'],
            'contacts' => ['url' => 'https://app.pennylane.com/api/external/v2/customers/42/contacts'],
        ];
    }

    public function testItBuildsFromTheDocumentedGetCompanyCustomerPayload(): void
    {
        $dto = CompanyCustomerDto::fromArray(self::documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('My Company', $dto->name);
        self::assertSame('FR1420041010050500013M02606', $dto->billingIban);
        self::assertSame(PaymentConditions::ThirtyDays, $dto->paymentConditions);
        self::assertSame('John Doe', $dto->recipient);
        self::assertSame('+33612345678', $dto->phone);
        self::assertSame('REF-1234', $dto->reference);
        self::assertSame('Some notes', $dto->notes);
        self::assertSame('FR12345678901', $dto->vatNumber);
        self::assertSame('123456789', $dto->regNo);
        self::assertSame(17, $dto->ledgerAccountId);
        self::assertSame(['hello@example.org'], $dto->emails);
        self::assertInstanceOf(Address::class, $dto->billingAddress);
        self::assertSame('Paris', $dto->billingAddress->city);
        self::assertInstanceOf(Address::class, $dto->deliveryAddress);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
        self::assertSame('0e67fc3c-c632-4feb-ad34-e18ed5fbf66a', $dto->externalReference);
        self::assertSame(BillingLanguage::FrFr, $dto->billingLanguage);
        self::assertSame('https://app.pennylane.com/api/external/v2/gocardless_mandates', $dto->mandatesUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/pro_account/mandates', $dto->proAccountMandatesUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42/contacts', $dto->contactsUrl);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['billing_iban'] = null;
        $payload['reference'] = null;
        $payload['notes'] = null;
        $payload['ledger_account'] = null;

        $dto = CompanyCustomerDto::fromArray($payload);

        self::assertNull($dto->billingIban);
        self::assertNull($dto->reference);
        self::assertNull($dto->notes);
        self::assertNull($dto->ledgerAccountId);
    }

    public function testItImplementsTheSharedCustomerDtoInterfaceAndExposesItsCustomerType(): void
    {
        $dto = CompanyCustomerDto::fromArray(self::documentedPayload());

        self::assertInstanceOf(CustomerDto::class, $dto);
        self::assertSame(CustomerType::Company, $dto->customerType());
        self::assertSame(42, $dto->id());
        self::assertSame('My Company', $dto->name());
    }
}
