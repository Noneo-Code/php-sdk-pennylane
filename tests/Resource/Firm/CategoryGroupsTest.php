<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryGroupDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CategoryGroupsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedGroupPayload(): array
    {
        return [
            'id' => 348,
            'label' => 'Achats',
            'categories' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/category_groups/348/categories'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedGroupPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->categoryGroups()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/category_groups' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(CategoryGroupDto::class, $page->items[0]);
        self::assertSame(348, $page->items[0]->id);
    }

    public function testGetSendsAGetRequestToTheCategoryGroupEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedGroupPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->categoryGroups()->get(348);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/category_groups/348' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testCategoriesSendsAGetRequestToTheCategoryGroupCategoriesEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->categoryGroups()->categories(348);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/category_groups/348/categories' === $response->getPendingRequest()->getUrl();
        });
    }
}
