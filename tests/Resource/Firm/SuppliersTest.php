<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class SuppliersTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Acme Corp',
            'establishment_no' => null,
            'reg_no' => null,
            'vat_number' => 'FR123456789',
            'ledger_account' => null,
            'emails' => ['billing@acme.test'],
            'iban' => 'FR1420041010050500013M02606',
            'postal_address' => ['address' => '1 rue de Paris', 'postal_code' => '75001', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            'supplier_payment_method' => null,
            'supplier_due_date_delay' => null,
            'supplier_due_date_rule' => null,
            'external_reference' => 'SUP-1',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->suppliers()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/suppliers' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(SupplierDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $suppliers = iterator_to_array($firm->company(1234)->suppliers()->paginate());

        self::assertCount(1, $suppliers);
        self::assertSame(42, $suppliers[0]->id);
    }
}
