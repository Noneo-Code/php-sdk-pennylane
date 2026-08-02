<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\Transaction\MatchedInvoiceDto;
use Noneo\Sdk\Pennylane\Dto\Transaction\TransactionDto;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class TransactionsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'VIR SEPA MY SUPPLIER SAS',
            'attachment_required' => true,
            'date' => '2023-08-30',
            'outstanding_balance' => '49.3',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'archived_at' => null,
            'currency' => 'EUR',
            'currency_amount' => '120.00',
            'amount' => '120.00',
            'currency_fee' => '12.00',
            'fee' => '12.00',
            'journal' => ['id' => 234, 'url' => 'https://app.pennylane.com/api/external/v2/journals/67'],
            'bank_account' => ['id' => 53, 'url' => 'https://app.pennylane.com/api/external/v2/bank_accounts/53'],
            'pro_account_expense' => null,
            'customer' => null,
            'supplier' => null,
            'categories' => [],
            'matched_invoices' => ['url' => 'https://app.pennylane.com/api/external/v2/transactions/42/matched_invoices'],
            'interbank_code' => null,
        ];
    }

    public function testListReturnsACursorPageOfTransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->transactions()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(TransactionDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testCreateReturnsATransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $transaction = $pennylane->transactions()->create(bankAccountId: 53, label: 'test label', date: '2025-07-30', amount: '120.00');

        self::assertSame(42, $transaction->id);
    }

    public function testGetReturnsATransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $transaction = $pennylane->transactions()->get(42);

        self::assertSame(42, $transaction->id);
    }

    public function testUpdateReturnsATransactionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $transaction = $pennylane->transactions()->update(42, customerId: 7);

        self::assertSame(42, $transaction->id);
    }

    public function testCategoriesReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [
                ['id' => 421, 'label' => 'HR - Salaries', 'weight' => '0.25', 'category_group' => ['id' => 229], 'analytical_code' => 'CODE123', 'created_at' => '2023-08-30T10:08:08.146343Z', 'updated_at' => '2023-08-30T10:08:08.146343Z'],
            ],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->transactions()->categories(42);

        self::assertCount(1, $page->items);
        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(421, $page->items[0]->id);
    }

    public function testCategorizeReturnsAListOfCategoryDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            ['id' => 426, 'label' => 'HR - Salaries', 'weight' => '0.6575', 'category_group' => ['id' => 229], 'analytical_code' => null, 'created_at' => '2023-08-30T10:08:08.146343Z', 'updated_at' => '2023-08-30T10:08:08.146343Z'],
        ])]);
        $pennylane->withMockClient($mockClient);

        $categories = $pennylane->transactions()->categorize(42, [new CategoryAllocation(426, '0.6575')]);

        self::assertCount(1, $categories);
        self::assertSame(426, $categories[0]->id);
    }

    public function testMatchedInvoicesReturnsACursorPageOfMatchedInvoiceDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [
                ['id' => 42, 'type' => 'customer', 'url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42'],
            ],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->transactions()->matchedInvoices(42);

        self::assertCount(1, $page->items);
        self::assertInstanceOf(MatchedInvoiceDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testMatchCustomerInvoiceSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->transactions()->matchCustomerInvoice(1234, 42);

        $mockClient->assertSentCount(1);
    }

    public function testUnmatchCustomerInvoiceSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->transactions()->unmatchCustomerInvoice(1234, 42);

        $mockClient->assertSentCount(1);
    }

    public function testMatchSupplierInvoiceSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->transactions()->matchSupplierInvoice(1234, 42);

        $mockClient->assertSentCount(1);
    }

    public function testUnmatchSupplierInvoiceSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->transactions()->unmatchSupplierInvoice(1234, 42);

        $mockClient->assertSentCount(1);
    }
}
