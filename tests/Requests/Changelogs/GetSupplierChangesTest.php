<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Changelogs;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetSupplierChanges;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetSupplierChangesTest extends TestCase
{
    public function testSendsAGetRequestToTheSuppliersChangelogEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetSupplierChanges(cursor: 'abc', limit: 10, startDate: '2025-06-25T11:54:18Z'));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/changelogs/suppliers' === $sent->resolveEndpoint()
                && [
                    'cursor' => 'abc',
                    'limit' => 10,
                    'start_date' => '2025-06-25T11:54:18Z',
                ] === $response->getPendingRequest()->query()->all();
        });
    }
}
