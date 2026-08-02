<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\PagePaginator;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class PagePaginatorTest extends TestCase
{
    private function makeConnector(MockClient $mockClient): Connector
    {
        $connector = new class extends Connector {
            public function resolveBaseUrl(): string
            {
                return 'https://app.pennylane.test/api/external/firm/v1';
            }
        };
        $connector->withMockClient($mockClient);

        return $connector;
    }

    private function makeRequest(): Request
    {
        return new class extends Request implements Paginatable {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies';
            }
        };
    }

    private function threePageMockClient(): MockClient
    {
        return new MockClient([
            MockResponse::make([
                'items' => [['id' => 1], ['id' => 2]],
                'total_pages' => 3,
                'current_page' => 1,
                'total_items' => 5,
                'per_page' => 2,
            ]),
            MockResponse::make([
                'items' => [['id' => 3], ['id' => 4]],
                'total_pages' => 3,
                'current_page' => 2,
                'total_items' => 5,
                'per_page' => 2,
            ]),
            MockResponse::make([
                'items' => [['id' => 5]],
                'total_pages' => 3,
                'current_page' => 3,
                'total_items' => 5,
                'per_page' => 2,
            ]),
        ]);
    }

    public function testItTraversesEveryMockedPageAndYieldsAllTheItems(): void
    {
        $mockClient = $this->threePageMockClient();
        $paginator = new PagePaginator($this->makeConnector($mockClient), $this->makeRequest());

        $items = iterator_to_array($paginator->items());

        self::assertCount(5, $items);
        self::assertSame(['id' => 1], $items[0]);
        self::assertSame(['id' => 5], $items[4]);
    }

    public function testFilterIsResentOnEveryFollowingPage(): void
    {
        $mockClient = $this->threePageMockClient();
        $paginator = new PagePaginator(
            $this->makeConnector($mockClient),
            $this->makeRequest(),
            filters: FilterSet::make()->eq('client_code', '42'),
        );

        $queriesPerPage = [];

        /** @var Response $response */
        foreach ($paginator as $response) {
            $queriesPerPage[] = $response->getPendingRequest()->query()->all();
        }

        self::assertCount(3, $queriesPerPage);

        foreach ([0, 1, 2] as $pageIndex) {
            self::assertSame(
                '[{"field":"client_code","operator":"eq","value":"42"}]',
                $queriesPerPage[$pageIndex]['filter'] ?? null,
                "La page {$pageIndex} doit renvoyer le filtre.",
            );
        }

        self::assertSame(1, $queriesPerPage[0]['page'] ?? null);
        self::assertSame(2, $queriesPerPage[1]['page'] ?? null);
        self::assertSame(3, $queriesPerPage[2]['page'] ?? null);
    }

    public function testIsLastPageReliesOnCurrentPageReachingTotalPages(): void
    {
        $mockClient = new MockClient([
            MockResponse::make([
                'items' => [],
                'total_pages' => 2,
                'current_page' => 1,
                'total_items' => 1,
                'per_page' => 20,
            ]),
            MockResponse::make([
                'items' => [['id' => 1]],
                'total_pages' => 2,
                'current_page' => 2,
                'total_items' => 1,
                'per_page' => 20,
            ]),
        ]);

        $paginator = new PagePaginator($this->makeConnector($mockClient), $this->makeRequest());

        $items = iterator_to_array($paginator->items());

        self::assertCount(1, $items);
        self::assertSame(['id' => 1], $items[0]);
    }
}
