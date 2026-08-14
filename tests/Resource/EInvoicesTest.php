<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportDto;
use Noneo\Sdk\Pennylane\Dto\EInvoice\EInvoiceImportType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\EInvoices\CreateEInvoiceImport;
use Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice\EInvoiceImportDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class EInvoicesTest extends TestCase
{
    public function testImportReturnsAnEInvoiceImportDtoAndSendsTheRequest(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(EInvoiceImportDtoTest::documentedGenericImportPayload(), 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $import = $pennylane->eInvoices()->import('%PDF-1.4 fake content', EInvoiceImportType::Supplier, 'facture.pdf');

        self::assertInstanceOf(EInvoiceImportDto::class, $import);
        self::assertSame(42, $import->id);
        self::assertSame('https://app.pennylane.com/api/external/v2/supplier_invoices/42', $import->url);
        $mockClient->assertSent(CreateEInvoiceImport::class);
    }
}
