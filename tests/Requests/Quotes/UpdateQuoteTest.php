<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Dto\Quote\QuoteInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\Quote\UpdateQuoteInvoiceLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\UpdateQuote;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateQuoteTest extends TestCase
{
    public function testSendsAPutRequestWithOnlyTheProvidedFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateQuote(id: 42, externalReference: 'FR123'));

        $mockClient->assertSent(function (UpdateQuote $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/quotes/42' === $sent->resolveEndpoint()
                && ['external_reference' => 'FR123'] === $sent->body()->all();
        });
    }

    public function testSendsTheInvoiceLinesPatch(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateQuote(
            id: 42,
            invoiceLines: new QuoteInvoiceLinesPatch(update: [new UpdateQuoteInvoiceLine(id: 1, label: 'Updated label')]),
        ));

        $mockClient->assertSent(function (UpdateQuote $sent): bool {
            return ['invoice_lines' => ['update' => [['id' => 1, 'label' => 'Updated label']]]] === $sent->body()->all();
        });
    }
}
