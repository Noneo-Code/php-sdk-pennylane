<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\PaRegistrations;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\PaRegistrations\GetPaRegistrations;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetPaRegistrationsTest extends TestCase
{
    public function testSendsAGetRequestToThePaRegistrationsEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetPaRegistrations());

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/pa_registrations' === $sent->resolveEndpoint();
        });
    }
}
