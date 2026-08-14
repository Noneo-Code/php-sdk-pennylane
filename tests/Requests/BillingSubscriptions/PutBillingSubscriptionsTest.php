<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BillingSubscriptions;

use Noneo\Sdk\Pennylane\Dto\BillingSubscription\InvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\UpdateInvoiceData;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\UpdateInvoiceLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\PutBillingSubscriptions;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutBillingSubscriptionsTest extends TestCase
{
    public function testSendsOnlyTheProvidedFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutBillingSubscriptions(42, stop: true));

        $mockClient->assertSent(function (PutBillingSubscriptions $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/billing_subscriptions/42' === $sent->resolveEndpoint()
                && ['stop' => true] === $sent->body()->all();
        });
    }

    public function testSendsAFalseStopToResumeTheSubscription(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutBillingSubscriptions(42, stop: false));

        $mockClient->assertSent(function (PutBillingSubscriptions $sent): bool {
            return ['stop' => false] === $sent->body()->all();
        });
    }

    public function testSendsTheModeAndInvoiceLinesPatch(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutBillingSubscriptions(
            42,
            mode: NewMode::awaitingValidation(),
            customerInvoiceData: new UpdateInvoiceData(
                invoiceLines: new InvoiceLinesPatch(
                    update: [new UpdateInvoiceLine(id: 7, label: 'Renamed line')],
                    delete: [9],
                ),
            ),
        ));

        $mockClient->assertSent(function (PutBillingSubscriptions $sent): bool {
            return [
                'mode' => ['type' => 'awaiting_validation'],
                'customer_invoice_data' => [
                    'invoice_lines' => [
                        'update' => [['id' => 7, 'label' => 'Renamed line']],
                        'delete' => [['id' => 9]],
                    ],
                ],
            ] === $sent->body()->all();
        });
    }
}
