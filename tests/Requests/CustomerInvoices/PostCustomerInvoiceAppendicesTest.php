<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PostCustomerInvoiceAppendices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class PostCustomerInvoiceAppendicesTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFile(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostCustomerInvoiceAppendices(42, '%PDF-1.4 fake content', 'invoice.pdf'));

        $mockClient->assertSent(function (PostCustomerInvoiceAppendices $sent): bool {
            $part = $sent->body()->all()[0];

            return Method::POST === $sent->getMethod()
                && '/customer_invoices/42/appendices' === $sent->resolveEndpoint()
                && 'file' === $part->name
                && 'invoice.pdf' === $part->filename
                && '%PDF-1.4 fake content' === $part->value;
        });
    }

    public function testSendsTheMultipartContentType(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostCustomerInvoiceAppendices(42, 'content', 'invoice.pdf'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $contentType = $response->getPendingRequest()->headers()->get('Content-Type');

            return is_string($contentType) && str_starts_with($contentType, 'multipart/form-data; boundary=');
        });
    }
}
