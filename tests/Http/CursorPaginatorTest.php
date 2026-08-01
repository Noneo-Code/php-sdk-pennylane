<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class CursorPaginatorTest extends TestCase
{
    private function makeConnector(MockClient $mockClient): Connector
    {
        $connector = new class extends Connector {
            public function resolveBaseUrl(): string
            {
                return 'https://app.pennylane.test/api/external/v2';
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
                return '/customer_invoices';
            }
        };
    }

    private function threePageMockClient(): MockClient
    {
        return new MockClient([
            MockResponse::make([
                'items' => [['id' => 1], ['id' => 2]],
                'has_more' => true,
                'next_cursor' => 'cursor-page-2',
            ]),
            MockResponse::make([
                'items' => [['id' => 3], ['id' => 4]],
                'has_more' => true,
                'next_cursor' => 'cursor-page-3',
            ]),
            MockResponse::make([
                'items' => [['id' => 5]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]);
    }

    public function testItTraversesEveryMockedPageAndYieldsAllTheItems(): void
    {
        $mockClient = $this->threePageMockClient();
        $paginator = new CursorPaginator($this->makeConnector($mockClient), $this->makeRequest());

        $items = iterator_to_array($paginator->items());

        self::assertCount(5, $items);
        self::assertSame(['id' => 1], $items[0]);
        self::assertSame(['id' => 5], $items[4]);
    }

    public function testFilterAndSortAreResentOnEveryFollowingPage(): void
    {
        $mockClient = $this->threePageMockClient();
        $paginator = new CursorPaginator(
            $this->makeConnector($mockClient),
            $this->makeRequest(),
            filters: FilterSet::make()->eq('status', 'draft'),
            sort: Sort::desc('date'),
        );

        $queriesPerPage = [];

        /** @var Response $response */
        foreach ($paginator as $response) {
            $queriesPerPage[] = $response->getPendingRequest()->query()->all();
        }

        self::assertCount(3, $queriesPerPage);

        foreach ([1, 2] as $followingPageIndex) {
            self::assertSame(
                '[{"field":"status","operator":"eq","value":"draft"}]',
                $queriesPerPage[$followingPageIndex]['filter'] ?? null,
                "La page {$followingPageIndex} doit renvoyer le filtre.",
            );
            self::assertSame(
                '-date',
                $queriesPerPage[$followingPageIndex]['sort'] ?? null,
                "La page {$followingPageIndex} doit renvoyer le tri.",
            );
        }

        self::assertSame('cursor-page-2', $queriesPerPage[1]['cursor'] ?? null);
        self::assertSame('cursor-page-3', $queriesPerPage[2]['cursor'] ?? null);
    }

    public function testIsLastPageReliesOnHasMoreRatherThanTheItemCount(): void
    {
        $mockClient = new MockClient([
            MockResponse::make([
                'items' => [],
                'has_more' => true,
                'next_cursor' => 'cursor-page-2',
            ]),
            MockResponse::make([
                'items' => [['id' => 1]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]);

        $paginator = new CursorPaginator($this->makeConnector($mockClient), $this->makeRequest());

        $items = iterator_to_array($paginator->items());

        self::assertCount(1, $items);
        self::assertSame(['id' => 1], $items[0]);
    }
}
