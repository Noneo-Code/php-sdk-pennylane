<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Dms;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\GetDmsFiles;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetDmsFilesTest extends TestCase
{
    public function testSendsAGetRequestToTheDmsFilesEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new GetDmsFiles());

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/dms/files' === $sent->resolveEndpoint()
                && [] === $response->getPendingRequest()->query()->all();
        });
    }

    public function testForwardsCursorLimitAndFilterAsQueryParameters(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new GetDmsFiles(cursor: 'abc', limit: 10, filters: FilterSet::make()->eq('parent_folder_id', '124')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return [
                'cursor' => 'abc',
                'limit' => 10,
                'filter' => '[{"field":"parent_folder_id","operator":"eq","value":"124"}]',
            ] === $response->getPendingRequest()->query()->all();
        });
    }
}
