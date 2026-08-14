<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CommercialDocuments;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\GetCommercialDocumentInvoiceLineSections;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCommercialDocumentInvoiceLineSectionsTest extends TestCase
{
    public function testSendsAGetRequestToTheInvoiceLineSectionsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetCommercialDocumentInvoiceLineSections(42, cursor: 'abc', limit: 10, sort: Sort::desc('id')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/commercial_documents/42/invoice_line_sections' === $sent->resolveEndpoint()
                && ['cursor' => 'abc', 'limit' => 10, 'sort' => '-id'] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testIsPaginatable(): void
    {
        self::assertInstanceOf(Paginatable::class, new GetCommercialDocumentInvoiceLineSections(42));
    }
}
