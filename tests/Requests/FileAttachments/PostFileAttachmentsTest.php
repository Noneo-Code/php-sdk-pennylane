<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\FileAttachments;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\FileAttachments\PostFileAttachments;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class PostFileAttachmentsTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFile(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostFileAttachments('%PDF-1.4 fake content', 'invoice.pdf'));

        $mockClient->assertSent(function (PostFileAttachments $sent): bool {
            $part = $sent->body()->all()[0];

            return Method::POST === $sent->getMethod()
                && '/file_attachments' === $sent->resolveEndpoint()
                && 'file' === $part->name
                && 'invoice.pdf' === $part->filename
                && '%PDF-1.4 fake content' === $part->value;
        });
    }

    public function testStreamsAResourceWithoutLoadingItIntoAString(): void
    {
        $resource = fopen('php://memory', 'r+b');
        self::assertIsResource($resource);
        fwrite($resource, '%PDF-1.4 fake content');
        rewind($resource);

        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostFileAttachments($resource, 'invoice.pdf'));

        $mockClient->assertSent(function (PostFileAttachments $sent) use ($resource): bool {
            $part = $sent->body()->all()[0];

            return is_resource($part->value) && $resource === $part->value;
        });
    }

    public function testSendsTheMultipartContentType(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostFileAttachments('content', 'invoice.pdf'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            $contentType = $response->getPendingRequest()->headers()->get('Content-Type');

            return is_string($contentType) && str_starts_with($contentType, 'multipart/form-data; boundary=');
        });
    }
}
