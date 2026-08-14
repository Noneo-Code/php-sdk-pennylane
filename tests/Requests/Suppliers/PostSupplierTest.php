<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Suppliers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDueDateRule;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierPaymentMethod;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PostSupplier;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostSupplierTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostSupplier(name: 'Pennylane'));

        $mockClient->assertSent(function (PostSupplier $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/suppliers' === $sent->resolveEndpoint()
                && ['name' => 'Pennylane'] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostSupplier(
            name: 'Pennylane',
            establishmentNo: '82762938500014',
            regNo: '827629385',
            postalAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            vatNumber: 'FR32123456789',
            ledgerAccountNumber: '411100344',
            emails: ['hello@example.org'],
            iban: 'FR3330002005500000157841Z25',
            supplierPaymentMethod: SupplierPaymentMethod::Check,
            supplierDueDateDelay: 30,
            supplierDueDateRule: SupplierDueDateRule::Days,
            externalReference: 'FR123',
        ));

        $mockClient->assertSent(function (PostSupplier $sent): bool {
            return [
                'name' => 'Pennylane',
                'establishment_no' => '82762938500014',
                'reg_no' => '827629385',
                'postal_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
                'vat_number' => 'FR32123456789',
                'ledger_account' => ['number' => '411100344'],
                'emails' => ['hello@example.org'],
                'iban' => 'FR3330002005500000157841Z25',
                'supplier_payment_method' => 'check',
                'supplier_due_date_delay' => 30,
                'supplier_due_date_rule' => 'days',
                'external_reference' => 'FR123',
            ] === $sent->body()->all();
        });
    }
}
