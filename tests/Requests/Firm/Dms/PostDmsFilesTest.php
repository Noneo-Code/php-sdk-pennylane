<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Dms;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\PostDmsFiles;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class PostDmsFilesTest extends TestCase
{
    public function testSendsAMultipartPostRequestToTheDmsFilesEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'name' => 'file.pdf',
            'path' => '/file.pdf',
            'parent_folder' => null,
            'url' => 'https://example.com/file.pdf',
            'created_at' => 'now',
            'updated_at' => 'now',
        ])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new PostDmsFiles('%PDF-1.4 content', 'file.pdf', 234));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::POST === $sent->getMethod() && '/dms/files' === $sent->resolveEndpoint();
        });
    }
}
