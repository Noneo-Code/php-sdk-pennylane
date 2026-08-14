<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Firm\Dms;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\ListDmsFolders;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class ListDmsFoldersTest extends TestCase
{
    public function testSendsAGetRequestToTheDmsFoldersEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new ListDmsFolders());

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/dms/folders' === $sent->resolveEndpoint();
        });
    }
}
