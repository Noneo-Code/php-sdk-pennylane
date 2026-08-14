<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\PostQuoteAppendices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class PostQuoteAppendicesTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFile(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostQuoteAppendices(42, '%PDF-1.4 fake content', 'quote.pdf'));

        $mockClient->assertSent(function (PostQuoteAppendices $sent): bool {
            $part = $sent->body()->all()[0];

            return Method::POST === $sent->getMethod()
                && '/quotes/42/appendices' === $sent->resolveEndpoint()
                && 'file' === $part->name
                && 'quote.pdf' === $part->filename
                && '%PDF-1.4 fake content' === $part->value;
        });
    }

    public function testSendsTheMultipartContentType(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostQuoteAppendices(42, 'content', 'quote.pdf'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $contentType = $response->getPendingRequest()->headers()->get('Content-Type');

            return is_string($contentType) && str_starts_with($contentType, 'multipart/form-data; boundary=');
        });
    }
}
