<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionDto;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionPaymentMethod;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewInvoiceData;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewRecurringRule;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\PostBillingSubscriptions;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\PutBillingSubscriptions;
use Noneo\Sdk\Pennylane\Tests\Dto\BillingSubscription\BillingSubscriptionDtoTest;
use Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\InvoiceLineDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class BillingSubscriptionsTest extends TestCase
{
    public function testListReturnsACursorPageOfBillingSubscriptionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [BillingSubscriptionDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->billingSubscriptions()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(BillingSubscriptionDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateYieldsBillingSubscriptionDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [BillingSubscriptionDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $subscriptions = iterator_to_array($pennylane->billingSubscriptions()->paginate());

        self::assertCount(1, $subscriptions);
        self::assertInstanceOf(BillingSubscriptionDto::class, $subscriptions[0]);
    }

    public function testGetReturnsABillingSubscriptionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make(BillingSubscriptionDtoTest::documentedPayload())]));

        $subscription = $pennylane->billingSubscriptions()->get(42);

        self::assertInstanceOf(BillingSubscriptionDto::class, $subscription);
        self::assertSame(42, $subscription->id);
    }

    public function testCreateReturnsABillingSubscriptionDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(BillingSubscriptionDtoTest::documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $subscription = $pennylane->billingSubscriptions()->create(
            start: '2023-01-01',
            mode: NewMode::finalized(),
            paymentConditions: PaymentConditions::UponReceipt,
            paymentMethod: BillingSubscriptionPaymentMethod::Offline,
            recurringRule: NewRecurringRule::monthly(interval: 1),
            customerId: 42,
            customerInvoiceData: new NewInvoiceData(invoiceLines: [
                new NewInvoiceLine(label: 'Demo label', quantity: 12, unit: 'piece', rawCurrencyUnitPrice: '33.33', vatRate: VatRate::Fr200),
            ]),
        );

        self::assertInstanceOf(BillingSubscriptionDto::class, $subscription);
        $mockClient->assertSent(PostBillingSubscriptions::class);
    }

    public function testUpdateReturnsABillingSubscriptionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(BillingSubscriptionDtoTest::documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $subscription = $pennylane->billingSubscriptions()->update(42, stop: true);

        self::assertInstanceOf(BillingSubscriptionDto::class, $subscription);
        $mockClient->assertSent(PutBillingSubscriptions::class);
    }

    public function testInvoiceLineSectionsReturnsACursorPageOfInvoiceLineSectionDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [[
                'id' => 444,
                'title' => 'Section #1',
                'description' => 'Lorem ipsum',
                'rank' => 1,
                'created_at' => '2023-08-30T10:08:08.146343Z',
                'updated_at' => '2023-08-30T10:08:08.146343Z',
            ]], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->billingSubscriptions()->invoiceLineSections(42);

        self::assertInstanceOf(InvoiceLineSectionDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLineSectionsYieldsInvoiceLineSectionDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [[
                'id' => 444,
                'title' => 'Section #1',
                'description' => 'Lorem ipsum',
                'rank' => 1,
                'created_at' => '2023-08-30T10:08:08.146343Z',
                'updated_at' => '2023-08-30T10:08:08.146343Z',
            ]], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $sections = iterator_to_array($pennylane->billingSubscriptions()->paginateInvoiceLineSections(42));

        self::assertCount(1, $sections);
        self::assertInstanceOf(InvoiceLineSectionDto::class, $sections[0]);
    }

    public function testInvoiceLinesReturnsACursorPageOfInvoiceLineDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [InvoiceLineDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->billingSubscriptions()->invoiceLines(42);

        self::assertInstanceOf(InvoiceLineDto::class, $page->items[0]);
    }

    public function testPaginateInvoiceLinesYieldsInvoiceLineDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [InvoiceLineDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $lines = iterator_to_array($pennylane->billingSubscriptions()->paginateInvoiceLines(42));

        self::assertCount(1, $lines);
        self::assertInstanceOf(InvoiceLineDto::class, $lines[0]);
    }
}
