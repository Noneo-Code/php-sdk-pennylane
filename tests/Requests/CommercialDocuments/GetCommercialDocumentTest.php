<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CommercialDocuments;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\GetCommercialDocument;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetCommercialDocumentTest extends TestCase
{
    public function testSendsAGetRequestToTheCommercialDocumentEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCommercialDocument(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/commercial_documents/42' === $sent->resolveEndpoint();
        });
    }
}
