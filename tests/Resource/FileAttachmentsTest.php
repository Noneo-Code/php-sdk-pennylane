<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\FileAttachment\FileAttachmentDto;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\FileAttachments\PostFileAttachments;
use Noneo\Sdk\Pennylane\Tests\Dto\FileAttachment\FileAttachmentDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class FileAttachmentsTest extends TestCase
{
    public function testUploadReturnsAFileAttachmentDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(FileAttachmentDtoTest::documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $fileAttachment = $pennylane->fileAttachments()->upload('%PDF-1.4 fake content', 'invoice.pdf');

        self::assertInstanceOf(FileAttachmentDto::class, $fileAttachment);
        self::assertSame(1, $fileAttachment->id);
        $mockClient->assertSent(PostFileAttachments::class);
    }
}
