<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\Customer\CompanyCustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\ContactDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerType;
use Noneo\Sdk\Pennylane\Dto\Customer\IndividualCustomerDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\PostCompanyCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PostIndividualCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PutCompanyCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PutCustomerCategories;
use Noneo\Sdk\Pennylane\Requests\Customers\PutIndividualCustomer;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CustomersTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedAddressPayload(): array
    {
        return ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'];
    }

    /** @return array<string, mixed> */
    private function documentedCompanyCustomerPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'My Company',
            'billing_iban' => null,
            'payment_conditions' => '30_days',
            'recipient' => 'John Doe',
            'phone' => '+33612345678',
            'reference' => null,
            'notes' => null,
            'vat_number' => 'FR12345678901',
            'reg_no' => '123456789',
            'ledger_account' => null,
            'emails' => ['hello@example.org'],
            'billing_address' => $this->documentedAddressPayload(),
            'delivery_address' => $this->documentedAddressPayload(),
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'external_reference' => '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
            'billing_language' => 'fr_FR',
            'mandates' => ['url' => 'https://app.pennylane.com/api/external/v2/gocardless_mandates'],
            'pro_account_mandates' => ['url' => 'https://app.pennylane.com/api/external/v2/pro_account/mandates'],
            'contacts' => ['url' => 'https://app.pennylane.com/api/external/v2/customers/42/contacts'],
        ];
    }

    /** @return array<string, mixed> */
    private function documentedIndividualCustomerPayload(): array
    {
        $payload = $this->documentedCompanyCustomerPayload();
        unset($payload['vat_number'], $payload['reg_no']);
        $payload['id'] = 43;
        $payload['name'] = 'John Doe';
        $payload['first_name'] = 'John';
        $payload['last_name'] = 'Doe';

        return $payload;
    }

    public function testGetCompanyReturnsACompanyCustomerDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedCompanyCustomerPayload())]));

        $customer = $pennylane->customers()->getCompany(42);

        self::assertInstanceOf(CompanyCustomerDto::class, $customer);
        self::assertSame(42, $customer->id);
    }

    public function testCreateCompanyReturnsACompanyCustomerDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedCompanyCustomerPayload(), 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $customer = $pennylane->customers()->createCompany(
            name: 'My Company',
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        );

        self::assertInstanceOf(CompanyCustomerDto::class, $customer);
        $mockClient->assertSent(function (PostCompanyCustomer $sent): bool {
            return [
                'name' => 'My Company',
                'billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            ] === $sent->body()->all();
        });
    }

    public function testUpdateCompanyReturnsACompanyCustomerDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedCompanyCustomerPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $customer = $pennylane->customers()->updateCompany(42, name: 'My Renamed Company');

        self::assertInstanceOf(CompanyCustomerDto::class, $customer);
        $mockClient->assertSent(function (PutCompanyCustomer $sent): bool {
            return ['name' => 'My Renamed Company'] === $sent->body()->all();
        });
    }

    public function testGetIndividualReturnsAnIndividualCustomerDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedIndividualCustomerPayload())]));

        $customer = $pennylane->customers()->getIndividual(43);

        self::assertInstanceOf(IndividualCustomerDto::class, $customer);
        self::assertSame(43, $customer->id);
    }

    public function testCreateIndividualReturnsAnIndividualCustomerDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedIndividualCustomerPayload(), 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $customer = $pennylane->customers()->createIndividual(
            firstName: 'John',
            lastName: 'Doe',
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        );

        self::assertInstanceOf(IndividualCustomerDto::class, $customer);
        $mockClient->assertSent(function (PostIndividualCustomer $sent): bool {
            return [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            ] === $sent->body()->all();
        });
    }

    public function testUpdateIndividualReturnsAnIndividualCustomerDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedIndividualCustomerPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $customer = $pennylane->customers()->updateIndividual(43, lastName: 'Smith');

        self::assertInstanceOf(IndividualCustomerDto::class, $customer);
        $mockClient->assertSent(function (PutIndividualCustomer $sent): bool {
            return ['last_name' => 'Smith'] === $sent->body()->all();
        });
    }

    public function testListReturnsACursorPageOfMixedCustomerDto(): void
    {
        $companyPayload = $this->documentedCompanyCustomerPayload();
        $companyPayload['customer_type'] = 'company';
        $individualPayload = $this->documentedIndividualCustomerPayload();
        $individualPayload['customer_type'] = 'individual';

        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$companyPayload, $individualPayload],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->customers()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertCount(2, $page->items);
        self::assertInstanceOf(CompanyCustomerDto::class, $page->items[0]);
        self::assertSame(CustomerType::Company, $page->items[0]->customerType());
        self::assertInstanceOf(IndividualCustomerDto::class, $page->items[1]);
        self::assertSame(CustomerType::Individual, $page->items[1]->customerType());
    }

    public function testPaginateYieldsCustomerDtoAcrossPages(): void
    {
        $companyPayload = $this->documentedCompanyCustomerPayload();
        $companyPayload['customer_type'] = 'company';

        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make(['items' => [$companyPayload], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $customers = iterator_to_array($pennylane->customers()->paginate());

        self::assertCount(1, $customers);
        self::assertInstanceOf(CompanyCustomerDto::class, $customers[0]);
    }

    public function testPaginateAsksForTheRequestedPageSize(): void
    {
        $companyPayload = $this->documentedCompanyCustomerPayload();
        $companyPayload['customer_type'] = 'company';

        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make(['items' => [$companyPayload], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        iterator_to_array($pennylane->customers()->paginate(perPage: 100));

        self::assertSame(100, $mockClient->getLastResponse()?->getPendingRequest()->query()->all()['limit'] ?? null);
    }

    public function testGetReturnsTheCorrectlyTypedCustomerDto(): void
    {
        $individualPayload = $this->documentedIndividualCustomerPayload();
        $individualPayload['customer_type'] = 'individual';

        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($individualPayload)]));

        $customer = $pennylane->customers()->get(43);

        self::assertInstanceOf(IndividualCustomerDto::class, $customer);
    }

    public function testContactsReturnsACursorPageOfContactDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [[
                    'id' => 421,
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'role' => 'accountant',
                    'email' => 'jane.doe@example.com',
                    'telephone_number' => '+33612345678',
                    'mobile_number' => '+33698765432',
                    'created_at' => '2023-08-30T10:08:08.146343Z',
                    'updated_at' => '2023-08-30T10:08:08.146343Z',
                ]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->customers()->contacts(42);

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(ContactDto::class, $page->items[0]);
        self::assertSame(421, $page->items[0]->id);
    }

    public function testPaginateContactsYieldsContactDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [[
                    'id' => 421,
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'role' => 'accountant',
                    'email' => 'jane.doe@example.com',
                    'telephone_number' => '+33612345678',
                    'mobile_number' => '+33698765432',
                    'created_at' => '2023-08-30T10:08:08.146343Z',
                    'updated_at' => '2023-08-30T10:08:08.146343Z',
                ]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $contacts = iterator_to_array($pennylane->customers()->paginateContacts(42));

        self::assertCount(1, $contacts);
        self::assertInstanceOf(ContactDto::class, $contacts[0]);
    }

    /** @return array<string, mixed> */
    private function documentedCategoryPayload(): array
    {
        return [
            'id' => 421,
            'label' => 'HR - Salaries',
            'weight' => '0.25',
            'category_group' => ['id' => 229],
            'analytical_code' => 'CODE123',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testCategoriesReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryPayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->customers()->categories(42);

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(421, $page->items[0]->id);
    }

    public function testPaginateCategoriesYieldsCategoryDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryPayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $categories = iterator_to_array($pennylane->customers()->paginateCategories(42));

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
    }

    public function testCategorizeReturnsCategoryDtoListAndSendsTheAllocations(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([$this->documentedCategoryPayload()])]);
        $pennylane->connector()->withMockClient($mockClient);

        $categories = $pennylane->customers()->categorize(42, [new CategoryAllocation(421, '0.25')]);

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
        $mockClient->assertSent(function (PutCustomerCategories $sent): bool {
            return [['id' => 421, 'weight' => '0.25']] === $sent->body()->all();
        });
    }
}
