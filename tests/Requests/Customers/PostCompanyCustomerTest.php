<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Customers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\PostCompanyCustomer;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostCompanyCustomerTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostCompanyCustomer(
            name: 'My Company',
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        ));

        $mockClient->assertSent(function (PostCompanyCustomer $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/company_customers' === $sent->resolveEndpoint()
                && [
                    'name' => 'My Company',
                    'billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostCompanyCustomer(
            name: 'My Company',
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            vatNumber: 'FR12345678901',
            regNo: '123456789',
            ledgerAccountNumber: '411100344',
            phone: '+33612345678',
            deliveryAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            paymentConditions: PaymentConditions::SixtyDays,
            billingIban: 'FR1420041010050500013M02606',
            recipient: 'John Doe',
            reference: 'REF-1234',
            notes: 'Some notes',
            emails: ['hello@example.org'],
            externalReference: '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
            billingLanguage: BillingLanguage::EnGb,
        ));

        $mockClient->assertSent(function (PostCompanyCustomer $sent): bool {
            return [
                'name' => 'My Company',
                'billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
                'vat_number' => 'FR12345678901',
                'reg_no' => '123456789',
                'ledger_account' => ['number' => '411100344'],
                'phone' => '+33612345678',
                'delivery_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
                'payment_conditions' => '60_days',
                'billing_iban' => 'FR1420041010050500013M02606',
                'recipient' => 'John Doe',
                'reference' => 'REF-1234',
                'notes' => 'Some notes',
                'emails' => ['hello@example.org'],
                'external_reference' => '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
                'billing_language' => 'en_GB',
            ] === $sent->body()->all();
        });
    }
}
