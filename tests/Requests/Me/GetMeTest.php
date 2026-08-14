<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Me;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Me\GetMe;
use Noneo\Sdk\Pennylane\Tests\Dto\Me\MeDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetMeTest extends TestCase
{
    public function testSendsAGetRequestToTheMeEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(MeDtoTest::documentedPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetMe());

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/me' === $sent->resolveEndpoint();
        });
    }
}
