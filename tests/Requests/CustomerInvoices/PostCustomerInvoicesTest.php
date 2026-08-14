<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\DiscountType;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\TransactionBankingProvider;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\TransactionProviderFieldName;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PostCustomerInvoices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostCustomerInvoicesTest extends TestCase
{
    public function testSendsADraftInvoiceWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostCustomerInvoices(
            date: '2023-08-30',
            deadline: '2023-09-30',
            customerId: 42,
            invoiceLines: [new NewProductInvoiceLine(productId: 3049, quantity: 12)],
            draft: true,
        ));

        $mockClient->assertSent(function (PostCustomerInvoices $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices' === $sent->resolveEndpoint()
                && [
                    'date' => '2023-08-30',
                    'deadline' => '2023-09-30',
                    'customer_id' => 42,
                    'draft' => true,
                    'invoice_lines' => [['product_id' => 3049, 'quantity' => 12]],
                ] === $sent->body()->all();
        });
    }

    public function testSendsAFinalizedInvoiceWithOptionalFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostCustomerInvoices(
            date: '2023-08-30',
            deadline: '2023-09-30',
            customerId: 42,
            invoiceLines: [
                new NewStandardInvoiceLine(
                    label: 'Demo label',
                    rawCurrencyUnitPrice: '33.333334',
                    unit: 'piece',
                    vatRate: VatRate::Fr200,
                    quantity: 12,
                ),
            ],
            currency: Currency::Usd,
            specialMention: 'Additional details',
            language: BillingLanguage::EnGb,
            discount: new Discount(DiscountType::Relative, '25'),
            label: 'Custom accounting label',
            externalReference: 'FR123',
            transactionReference: new NewTransactionReference(
                TransactionBankingProvider::Bank,
                TransactionProviderFieldName::Label,
                'invoice_number',
            ),
        ));

        $mockClient->assertSent(function (PostCustomerInvoices $sent): bool {
            return [
                'date' => '2023-08-30',
                'deadline' => '2023-09-30',
                'customer_id' => 42,
                'currency' => 'USD',
                'special_mention' => 'Additional details',
                'language' => 'en_GB',
                'discount' => ['type' => 'relative', 'value' => '25'],
                'label' => 'Custom accounting label',
                'external_reference' => 'FR123',
                'transaction_reference' => ['banking_provider' => 'bank', 'provider_field_name' => 'label', 'provider_field_value' => 'invoice_number'],
                'invoice_lines' => [[
                    'label' => 'Demo label',
                    'raw_currency_unit_price' => '33.333334',
                    'unit' => 'piece',
                    'vat_rate' => 'FR_200',
                    'quantity' => 12,
                ]],
            ] === $sent->body()->all();
        });
    }
}
