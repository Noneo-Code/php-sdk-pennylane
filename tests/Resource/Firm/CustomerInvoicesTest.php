<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\CustomerInvoiceDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CustomerInvoicesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return CustomerInvoiceDtoTest::documentedPayload();
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->customerInvoices()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && 'https://app.pennylane.com/api/external/firm/v1/companies/1234/customer_invoices' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(CustomerInvoiceDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testListForwardsCursorLimitFilterAndSortAsQuery(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->customerInvoices()->list(
            cursor: 'abc',
            limit: 20,
            filters: FilterSet::make()->eq('id', '42'),
            sort: Sort::desc('id'),
        );

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $query = $response->getPendingRequest()->query()->all();

            return 'abc' === $query['cursor']
                && 20 === $query['limit']
                && '[{"field":"id","operator":"eq","value":"42"}]' === $query['filter']
                && '-id' === $query['sort'];
        });
    }

    public function testGetSendsAGetRequestOnTheScopedInvoiceUrl(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $invoice = $firm->company(1234)->customerInvoices()->get(42);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && 'https://app.pennylane.com/api/external/firm/v1/companies/1234/customer_invoices/42' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(CustomerInvoiceDto::class, $invoice);
        self::assertSame(42, $invoice->id);
    }

    public function testPaginateTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $invoices = iterator_to_array($firm->company(1234)->customerInvoices()->paginate());

        self::assertCount(1, $invoices);
        self::assertSame(42, $invoices[0]->id);
    }
}
