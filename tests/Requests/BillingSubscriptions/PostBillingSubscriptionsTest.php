<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BillingSubscriptions;

use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionPaymentMethod;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewInvoiceData;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewRecurringRule;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\PostBillingSubscriptions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostBillingSubscriptionsTest extends TestCase
{
    public function testSendsARequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostBillingSubscriptions(
            start: '2023-01-01',
            mode: NewMode::finalized(),
            paymentConditions: PaymentConditions::UponReceipt,
            paymentMethod: BillingSubscriptionPaymentMethod::Offline,
            recurringRule: NewRecurringRule::monthly(interval: 1),
            customerId: 42,
            customerInvoiceData: new NewInvoiceData(
                invoiceLines: [new NewInvoiceLine(
                    label: 'Demo label',
                    quantity: 12,
                    unit: 'piece',
                    rawCurrencyUnitPrice: '33.333334',
                    vatRate: VatRate::Fr200,
                )],
            ),
        ));

        $mockClient->assertSent(function (PostBillingSubscriptions $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/billing_subscriptions' === $sent->resolveEndpoint()
                && [
                    'start' => '2023-01-01',
                    'mode' => ['type' => 'finalized'],
                    'payment_conditions' => 'upon_receipt',
                    'payment_method' => 'offline',
                    'recurring_rule' => ['type' => 'monthly', 'interval' => 1],
                    'customer_id' => 42,
                    'customer_invoice_data' => [
                        'invoice_lines' => [[
                            'label' => 'Demo label',
                            'quantity' => 12,
                            'unit' => 'piece',
                            'raw_currency_unit_price' => '33.333334',
                            'vat_rate' => 'FR_200',
                        ]],
                    ],
                ] === $sent->body()->all();
        });
    }

    public function testSendsAnEmailModeWithRecipients(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostBillingSubscriptions(
            start: '2023-01-01',
            mode: NewMode::email(['customer@example.com']),
            paymentConditions: PaymentConditions::ThirtyDays,
            paymentMethod: BillingSubscriptionPaymentMethod::GocardlessDirectDebit,
            recurringRule: NewRecurringRule::yearly(count: 12),
            customerId: 42,
            customerInvoiceData: new NewInvoiceData(invoiceLines: []),
            label: 'Custom label',
        ));

        $mockClient->assertSent(function (PostBillingSubscriptions $sent): bool {
            $body = $sent->body()->all();

            return ['type' => 'email', 'email_settings' => ['recipients' => ['customer@example.com']]] === $body['mode']
                && ['type' => 'yearly', 'count' => 12] === $body['recurring_rule']
                && 'Custom label' === $body['label'];
        });
    }
}
