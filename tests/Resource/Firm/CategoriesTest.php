<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CategoriesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'archived_at' => null,
            'category_group' => ['id' => 348],
            'analytical_code' => 'CODE123',
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->categories()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/categories' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testGetSendsAGetRequestToTheCategoryEndpoint(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->categories()->get(42);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/categories/42' === $response->getPendingRequest()->getUrl();
        });
    }
}
