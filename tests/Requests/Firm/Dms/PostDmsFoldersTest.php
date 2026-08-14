<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Dms;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\PostDmsFolders;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostDmsFoldersTest extends TestCase
{
    public function testSendsAPostRequestWithTheFolderBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 124,
            'name' => 'Folder 1',
            'path' => '/Folder 1',
            'parent_folder' => null,
            'files' => ['url' => 'https://example.com/dms/files'],
            'created_at' => 'now',
            'updated_at' => 'now',
        ])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new PostDmsFolders('Folder 1', 123));

        $mockClient->assertSent(function (PostDmsFolders $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/dms/folders' === $sent->resolveEndpoint()
                && ['name' => 'Folder 1', 'parent_folder_id' => 123] === $sent->body()->all();
        });
    }
}
