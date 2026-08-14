<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\SendByEmailCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class SendByEmailCustomerInvoiceTest extends TestCase
{
    public function testSendsAPostRequestToTheSendByEmailEndpointWithoutRecipients(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new SendByEmailCustomerInvoice(42));

        $mockClient->assertSent(function (SendByEmailCustomerInvoice $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices/42/send_by_email' === $sent->resolveEndpoint()
                && [] === $sent->body()->all();
        });
    }

    public function testSendsTheRecipientsInTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new SendByEmailCustomerInvoice(42, ['jane@example.com', 'john@example.com']));

        $mockClient->assertSent(function (SendByEmailCustomerInvoice $sent): bool {
            return ['recipients' => ['jane@example.com', 'john@example.com']] === $sent->body()->all();
        });
    }
}
