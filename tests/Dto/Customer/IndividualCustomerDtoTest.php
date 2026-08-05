<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerType;
use Noneo\Sdk\Pennylane\Dto\Customer\IndividualCustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class IndividualCustomerDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getIndividualCustomer')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'John Doe',
            'billing_iban' => 'FR1420041010050500013M02606',
            'payment_conditions' => '30_days',
            'recipient' => 'John Doe',
            'phone' => '+33612345678',
            'reference' => 'REF-1234',
            'notes' => 'Some notes',
            'first_name' => 'John',
            'last_name' => 'Doe',
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

    public function testItBuildsFromTheDocumentedGetIndividualCustomerPayload(): void
    {
        $dto = IndividualCustomerDto::fromArray(self::documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('John Doe', $dto->name);
        self::assertSame('John', $dto->firstName);
        self::assertSame('Doe', $dto->lastName);
        self::assertSame(PaymentConditions::ThirtyDays, $dto->paymentConditions);
        self::assertSame(17, $dto->ledgerAccountId);
        self::assertSame(['hello@example.org'], $dto->emails);
        self::assertInstanceOf(Address::class, $dto->billingAddress);
        self::assertInstanceOf(Address::class, $dto->deliveryAddress);
        self::assertSame(BillingLanguage::FrFr, $dto->billingLanguage);
        self::assertSame('https://app.pennylane.com/api/external/v2/gocardless_mandates', $dto->mandatesUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/pro_account/mandates', $dto->proAccountMandatesUrl);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42/contacts', $dto->contactsUrl);
    }

    public function testItImplementsTheSharedCustomerDtoInterfaceAndExposesItsCustomerType(): void
    {
        $dto = IndividualCustomerDto::fromArray(self::documentedPayload());

        self::assertInstanceOf(CustomerDto::class, $dto);
        self::assertSame(CustomerType::Individual, $dto->customerType());
        self::assertSame(42, $dto->id());
        self::assertSame('John Doe', $dto->name());
    }
}
