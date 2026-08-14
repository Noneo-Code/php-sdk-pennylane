<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\EInvoices;

use Noneo\Sdk\Pennylane\Dto\EInvoice\EInvoiceImportType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\EInvoices\CreateEInvoiceImport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CreateEInvoiceImportTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFileAndTheType(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/'], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateEInvoiceImport('%PDF-1.4 fake content', EInvoiceImportType::Customer, 'facture.pdf'));

        $mockClient->assertSent(function (CreateEInvoiceImport $sent): bool {
            [$file, $type] = $sent->body()->all();

            return Method::POST === $sent->getMethod()
                && '/e-invoices/imports' === $sent->resolveEndpoint()
                && 'file' === $file->name
                && 'facture.pdf' === $file->filename
                && '%PDF-1.4 fake content' === $file->value
                && 'type' === $type->name
                && 'customer' === $type->value;
        });
    }

    public function testSendsTheSupplierType(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/'], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateEInvoiceImport('content', EInvoiceImportType::Supplier, 'facture.xml'));

        $mockClient->assertSent(function (CreateEInvoiceImport $sent): bool {
            $type = $sent->body()->all()[1];

            return 'supplier' === $type->value;
        });
    }

    public function testStreamsAResourceWithoutLoadingItIntoAString(): void
    {
        $resource = fopen('php://memory', 'r+b');
        self::assertIsResource($resource);
        fwrite($resource, '%PDF-1.4 fake content');
        rewind($resource);

        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/'], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateEInvoiceImport($resource, EInvoiceImportType::Supplier, 'facture.pdf'));

        $mockClient->assertSent(function (CreateEInvoiceImport $sent) use ($resource): bool {
            $file = $sent->body()->all()[0];

            return is_resource($file->value) && $resource === $file->value;
        });
    }

    public function testSendsTheMultipartContentType(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/'], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateEInvoiceImport('content', EInvoiceImportType::Customer, 'facture.pdf'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $contentType = $response->getPendingRequest()->headers()->get('Content-Type');

            return is_string($contentType) && str_starts_with($contentType, 'multipart/form-data; boundary=');
        });
    }
}
