<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\PostQuotes;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostQuotesTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostQuotes(
            date: '2020-01-01',
            deadline: '2020-12-31',
            customerId: 42,
            invoiceLines: [new NewQuoteProductInvoiceLine(productId: 3049, quantity: 12)],
        ));

        $mockClient->assertSent(function (PostQuotes $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/quotes' === $sent->resolveEndpoint()
                && [
                    'date' => '2020-01-01',
                    'deadline' => '2020-12-31',
                    'customer_id' => 42,
                    'invoice_lines' => [['product_id' => 3049, 'quantity' => 12]],
                ] === $sent->body()->all();
        });
    }

    public function testSendsInvoiceLineSectionsAndStandardLines(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostQuotes(
            date: '2020-01-01',
            deadline: '2020-12-31',
            customerId: 42,
            invoiceLines: [
                new NewQuoteStandardInvoiceLine(
                    label: 'Consulting fees',
                    rawCurrencyUnitPrice: '33.333334',
                    unit: 'piece',
                    vatRate: VatRate::Fr200,
                    quantity: 2,
                ),
            ],
            externalReference: 'FR123',
        ));

        $mockClient->assertSent(function (PostQuotes $sent): bool {
            return [
                'date' => '2020-01-01',
                'deadline' => '2020-12-31',
                'customer_id' => 42,
                'external_reference' => 'FR123',
                'invoice_lines' => [[
                    'label' => 'Consulting fees',
                    'quantity' => 2,
                    'raw_currency_unit_price' => '33.333334',
                    'unit' => 'piece',
                    'vat_rate' => 'FR_200',
                ]],
            ] === $sent->body()->all();
        });
    }
}
