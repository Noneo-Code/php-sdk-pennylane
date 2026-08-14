<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class FileAttachmentsTest extends TestCase
{
    public function testUploadSendsAPostRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 1,
            'url' => 'https://app.pennylane.com/files/1',
            'filename' => 'invoice.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ])]);
        $firm->connector()->withMockClient($mockClient);

        $attachment = $firm->company(1234)->fileAttachments()->upload('%PDF-1.4 content', 'invoice.pdf');

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/file_attachments' === $response->getPendingRequest()->getUrl();
        });

        self::assertSame(1, $attachment->id);
    }
}
