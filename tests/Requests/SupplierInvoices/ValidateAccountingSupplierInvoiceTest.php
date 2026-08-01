<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\ValidateAccountingSupplierInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class ValidateAccountingSupplierInvoiceTest extends TestCase
{
    public function testSendsAPutRequestToTheValidateAccountingEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 12345])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new ValidateAccountingSupplierInvoice(12345));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::PUT === $sent->getMethod() && '/supplier_invoices/12345/validate_accounting' === $sent->resolveEndpoint();
        });
    }
}
