<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http;

use Noneo\Sdk\Pennylane\Http\ChangelogPaginator;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetCustomerInvoicesChanges;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class ChangelogPaginatorTest extends TestCase
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

    public function testStartDateIsSentOnTheFirstPageOnlyAndCursorTakesOverAfterwards(): void
    {
        $mockClient = new MockClient([
            MockResponse::make([
                'items' => [['id' => 1]],
                'has_more' => true,
                'next_cursor' => 'cursor-page-2',
            ]),
            MockResponse::make([
                'items' => [['id' => 2]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]);

        $paginator = new ChangelogPaginator(
            $this->makeConnector($mockClient),
            new GetCustomerInvoicesChanges(startDate: '2025-06-25T11:54:18Z'),
        );

        $queriesPerPage = [];

        /** @var Response $response */
        foreach ($paginator as $response) {
            $queriesPerPage[] = $response->getPendingRequest()->query()->all();
        }

        self::assertCount(2, $queriesPerPage);
        self::assertSame('2025-06-25T11:54:18Z', $queriesPerPage[0]['start_date'] ?? null);
        self::assertArrayNotHasKey('cursor', $queriesPerPage[0]);

        self::assertArrayNotHasKey('start_date', $queriesPerPage[1]);
        self::assertSame('cursor-page-2', $queriesPerPage[1]['cursor'] ?? null);
    }

    public function testItTraversesEveryMockedPageAndYieldsAllTheItems(): void
    {
        $mockClient = new MockClient([
            MockResponse::make(['items' => [['id' => 1], ['id' => 2]], 'has_more' => true, 'next_cursor' => 'cursor-page-2']),
            MockResponse::make(['items' => [['id' => 3]], 'has_more' => false, 'next_cursor' => null]),
        ]);

        $paginator = new ChangelogPaginator($this->makeConnector($mockClient), new GetCustomerInvoicesChanges());

        $items = iterator_to_array($paginator->items());

        self::assertCount(3, $items);
        self::assertSame(['id' => 1], $items[0]);
        self::assertSame(['id' => 3], $items[2]);
    }
}
