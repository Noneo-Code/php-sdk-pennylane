<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceLineOptions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\OverrideInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\CreateCustomerInvoiceEInvoiceImport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CreateCustomerInvoiceEInvoiceImportTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFile(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/x'], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreateCustomerInvoiceEInvoiceImport('%PDF-1.4 fake content', 'invoice.pdf'));

        $mockClient->assertSent(function (CreateCustomerInvoiceEInvoiceImport $sent): bool {
            $parts = $sent->body()->all();

            return Method::POST === $sent->getMethod()
                && '/customer_invoices/e_invoices/imports' === $sent->resolveEndpoint()
                && 1 === count($parts)
                && 'file' === $parts[0]->name
                && 'invoice.pdf' === $parts[0]->filename
                && '%PDF-1.4 fake content' === $parts[0]->value;
        });
    }

    public function testSendsTheMultipartContentType(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/x'], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreateCustomerInvoiceEInvoiceImport('content', 'invoice.pdf'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $contentType = $response->getPendingRequest()->headers()->get('Content-Type');

            return is_string($contentType) && str_starts_with($contentType, 'multipart/form-data; boundary=');
        });
    }

    public function testSendsTheSendToPaFlagAsAMultipartPart(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/x'], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreateCustomerInvoiceEInvoiceImport('content', 'invoice.pdf', sendToPa: true));

        $mockClient->assertSent(function (CreateCustomerInvoiceEInvoiceImport $sent): bool {
            $parts = $sent->body()->all();
            $sendToPaPart = null;
            foreach ($parts as $part) {
                if ('send_to_pa' === $part->name) {
                    $sendToPaPart = $part;
                }
            }

            return null !== $sendToPaPart && 'true' === $sendToPaPart->value;
        });
    }

    public function testSendsInvoiceOptionsAsAJsonEncodedMultipartPart(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/x'], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $options = new EInvoiceImportOptions(
            customerId: 12,
            invoiceLines: [new EInvoiceLineOptions(eInvoiceLineId: 'BT-126-1', ledgerAccountId: 123)],
        );

        $pennylane->connector()->send(new CreateCustomerInvoiceEInvoiceImport('content', 'invoice.pdf', invoiceOptions: $options));

        $mockClient->assertSent(function (CreateCustomerInvoiceEInvoiceImport $sent): bool {
            $parts = $sent->body()->all();
            $optionsPart = null;
            foreach ($parts as $part) {
                if ('invoice_options' === $part->name) {
                    $optionsPart = $part;
                }
            }

            return null !== $optionsPart
                && 'application/json' === ($optionsPart->headers['Content-Type'] ?? null)
                && json_encode(['customer_id' => 12, 'invoice_lines' => [['e_invoice_line_id' => 'BT-126-1', 'ledger_account_id' => 123]]]) === $optionsPart->value;
        });
    }

    public function testSendsOverrideInvoiceLinesAsAJsonEncodedMultipartPart(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/x'], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $lines = [new OverrideInvoiceLine(
            currencyAmount: '50.00',
            currencyTax: '10.00',
            vatRate: VatRate::Fr200,
            ledgerAccountId: 706,
            quantity: 1,
            rawCurrencyUnitPrice: '50.00',
        )];

        $pennylane->connector()->send(new CreateCustomerInvoiceEInvoiceImport('content', 'invoice.pdf', overrideInvoiceLines: $lines));

        $mockClient->assertSent(function (CreateCustomerInvoiceEInvoiceImport $sent): bool {
            $parts = $sent->body()->all();
            $linesPart = null;
            foreach ($parts as $part) {
                if ('override_invoice_lines' === $part->name) {
                    $linesPart = $part;
                }
            }

            return null !== $linesPart
                && 'application/json' === ($linesPart->headers['Content-Type'] ?? null)
                && json_encode([[
                    'currency_amount' => '50.00',
                    'currency_tax' => '10.00',
                    'vat_rate' => 'FR_200',
                    'ledger_account_id' => 706,
                    'quantity' => 1,
                    'raw_currency_unit_price' => '50.00',
                ]]) === $linesPart->value;
        });
    }
}
