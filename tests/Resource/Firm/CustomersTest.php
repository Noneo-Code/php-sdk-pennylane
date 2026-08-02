<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Customer\CompanyCustomerDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CustomersTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedAddressPayload(): array
    {
        return ['address' => '1 rue de Paris', 'postal_code' => '75001', 'city' => 'Paris', 'country_alpha2' => 'FR'];
    }

    /** @return array<string, mixed> */
    private function documentedCompanyCustomerPayload(): array
    {
        return [
            'customer_type' => 'company',
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
            'mandates' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/gocardless_mandates'],
            'pro_account_mandates' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/pro_account/mandates'],
            'contacts' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/customers/42/contacts'],
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedCompanyCustomerPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->customers()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/customers' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(CompanyCustomerDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedCompanyCustomerPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $customers = iterator_to_array($firm->company(1234)->customers()->paginate());

        self::assertCount(1, $customers);
        self::assertInstanceOf(CompanyCustomerDto::class, $customers[0]);
    }
}
