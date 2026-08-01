<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\LinkCreditNote;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class LinkCreditNoteTest extends TestCase
{
    public function testSendsAPostRequestWithTheCreditNoteId(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new LinkCreditNote(42, 7));

        $mockClient->assertSent(function (LinkCreditNote $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices/42/link_credit_note' === $sent->resolveEndpoint()
                && ['credit_note_id' => 7] === $sent->body()->all();
        });
    }
}
