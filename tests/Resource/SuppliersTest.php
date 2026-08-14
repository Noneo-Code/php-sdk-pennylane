<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDto;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PostSupplier;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PutSupplier;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PutSupplierCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class SuppliersTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedSupplierPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Pennylane',
            'establishment_no' => '82762938500014',
            'reg_no' => '827629385',
            'vat_number' => 'FR32123456789',
            'ledger_account' => null,
            'emails' => ['hello@example.org'],
            'iban' => 'FRXXXXXXXXXXXXXXXXXXXXXXXXX',
            'postal_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            'supplier_payment_method' => null,
            'supplier_due_date_delay' => null,
            'supplier_due_date_rule' => null,
            'external_reference' => 'FR123',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
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

    public function testListReturnsACursorPageOfSupplierDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedSupplierPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->suppliers()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(SupplierDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateYieldsSupplierDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedSupplierPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $suppliers = iterator_to_array($pennylane->suppliers()->paginate());

        self::assertCount(1, $suppliers);
        self::assertInstanceOf(SupplierDto::class, $suppliers[0]);
    }

    public function testGetReturnsASupplierDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedSupplierPayload())]));

        $supplier = $pennylane->suppliers()->get(42);

        self::assertInstanceOf(SupplierDto::class, $supplier);
        self::assertSame(42, $supplier->id);
    }

    public function testCreateReturnsASupplierDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierPayload(), 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $supplier = $pennylane->suppliers()->create(
            name: 'Pennylane',
            postalAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        );

        self::assertInstanceOf(SupplierDto::class, $supplier);
        $mockClient->assertSent(function (PostSupplier $sent): bool {
            return [
                'name' => 'Pennylane',
                'postal_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            ] === $sent->body()->all();
        });
    }

    public function testUpdateReturnsASupplierDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $supplier = $pennylane->suppliers()->update(42, name: 'Renamed Pennylane');

        self::assertInstanceOf(SupplierDto::class, $supplier);
        $mockClient->assertSent(function (PutSupplier $sent): bool {
            return ['name' => 'Renamed Pennylane'] === $sent->body()->all();
        });
    }

    public function testUpdateClearsAFieldWhenAskedTo(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedSupplierPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->suppliers()->update(42, establishmentNo: Clear::value());

        $mockClient->assertSent(function (PutSupplier $sent): bool {
            return ['establishment_no' => null] === $sent->body()->all();
        });
    }

    public function testCategoriesReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedCategoryPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->suppliers()->categories(42);

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(421, $page->items[0]->id);
    }

    public function testPaginateCategoriesYieldsCategoryDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make(['items' => [$this->documentedCategoryPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $categories = iterator_to_array($pennylane->suppliers()->paginateCategories(42));

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
    }

    public function testCategorizeReturnsCategoryDtoListAndSendsTheAllocations(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([$this->documentedCategoryPayload()])]);
        $pennylane->connector()->withMockClient($mockClient);

        $categories = $pennylane->suppliers()->categorize(42, [new CategoryAllocation(421, '0.25')]);

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
        $mockClient->assertSent(function (PutSupplierCategories $sent): bool {
            return [['id' => 421, 'weight' => '0.25']] === $sent->body()->all();
        });
    }
}
