<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\DeleteSepaMandate;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class DeleteSepaMandateTest extends TestCase
{
    public function testSendsADeleteRequestToTheSepaMandateEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new DeleteSepaMandate(12345));

        $mockClient->assertSent(function (DeleteSepaMandate $sent): bool {
            return Method::DELETE === $sent->getMethod()
                && '/sepa_mandates/12345' === $sent->resolveEndpoint();
        });
    }
}
