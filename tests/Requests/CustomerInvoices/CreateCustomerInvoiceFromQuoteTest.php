<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\CreateCustomerInvoiceFromQuote;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CreateCustomerInvoiceFromQuoteTest extends TestCase
{
    public function testSendsAPostRequestWithTheQuoteIdAndDraft(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateCustomerInvoiceFromQuote(42, true));

        $mockClient->assertSent(function (CreateCustomerInvoiceFromQuote $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices/create_from_quote' === $sent->resolveEndpoint()
                && ['quote_id' => 42, 'draft' => true] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateCustomerInvoiceFromQuote(42, false, 'FR123', 9));

        $mockClient->assertSent(function (CreateCustomerInvoiceFromQuote $sent): bool {
            return [
                'quote_id' => 42,
                'draft' => false,
                'external_reference' => 'FR123',
                'customer_invoice_template_id' => 9,
            ] === $sent->body()->all();
        });
    }
}
