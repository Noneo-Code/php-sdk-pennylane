<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CategoryGroups;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CategoryGroups\GetCategoryGroup;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetCategoryGroupTest extends TestCase
{
    public function testSendsAGetRequestToTheCategoryGroupEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 989])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCategoryGroup(989));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/category_groups/989' === $sent->resolveEndpoint();
        });
    }
}
