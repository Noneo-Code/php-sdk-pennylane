<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Dms\DmsFileDto;
use Noneo\Sdk\Pennylane\Dto\Dms\DmsFolderDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\PostDmsFolders;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class DmsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedFilePayload(): array
    {
        return [
            'id' => 124,
            'name' => 'file.pdf',
            'path' => '/file.pdf',
            'parent_folder' => null,
            'url' => 'https://example.com/file.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    /** @return array<string, mixed> */
    private function documentedFolderPayload(): array
    {
        return [
            'id' => 234,
            'name' => 'Folder 1',
            'path' => '/Folder 1',
            'parent_folder' => null,
            'files' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/dms/files'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testFilesSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedFilePayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->dms()->files();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/dms/files' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(DmsFileDto::class, $page->items[0]);
        self::assertSame(124, $page->items[0]->id);
    }

    public function testUploadFileSendsAMultipartPostRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedFilePayload())]);
        $firm->withMockClient($mockClient);

        $file = $firm->company(1234)->dms()->uploadFile('%PDF-1.4 content', 'file.pdf');

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/dms/files' === $response->getPendingRequest()->getUrl();
        });

        self::assertSame(124, $file->id);
    }

    public function testFoldersSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedFolderPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->dms()->folders();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/dms/folders' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(DmsFolderDto::class, $page->items[0]);
        self::assertSame(234, $page->items[0]->id);
    }

    public function testCreateFolderSendsAPostRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedFolderPayload())]);
        $firm->withMockClient($mockClient);

        $folder = $firm->company(1234)->dms()->createFolder('Folder 1');

        $mockClient->assertSent(function (PostDmsFolders $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/dms/folders' === $response->getPendingRequest()->getUrl()
                && ['name' => 'Folder 1'] === $sent->body()->all();
        });

        self::assertSame(234, $folder->id);
    }
}
