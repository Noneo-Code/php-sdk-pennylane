<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CommercialDocuments;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CommercialDocuments\PostCommercialDocumentAppendices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostCommercialDocumentAppendicesTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFile(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostCommercialDocumentAppendices(42, '%PDF-1.4 fake content', 'appendix.pdf'));

        $mockClient->assertSent(function (PostCommercialDocumentAppendices $sent): bool {
            $part = $sent->body()->all()[0];

            return Method::POST === $sent->getMethod()
                && '/commercial_documents/42/appendices' === $sent->resolveEndpoint()
                && 'file' === $part->name
                && 'appendix.pdf' === $part->filename
                && '%PDF-1.4 fake content' === $part->value;
        });
    }
}
