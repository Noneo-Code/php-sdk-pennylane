<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PostSupplierInvoiceLinkedPurchaseRequests;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostSupplierInvoiceLinkedPurchaseRequestsTest extends TestCase
{
    public function testSendsAPostRequestWithThePurchaseRequestId(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostSupplierInvoiceLinkedPurchaseRequests(123, 42));

        $mockClient->assertSent(function (PostSupplierInvoiceLinkedPurchaseRequests $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/supplier_invoices/123/linked_purchase_requests' === $sent->resolveEndpoint()
                && ['purchase_request_id' => 42] === $sent->body()->all();
        });
    }
}
