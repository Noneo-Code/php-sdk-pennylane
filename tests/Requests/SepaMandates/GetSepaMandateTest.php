<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\GetSepaMandate;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class GetSepaMandateTest extends TestCase
{
    public function testSendsAGetRequestToTheSepaMandateEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetSepaMandate(12345));

        $mockClient->assertSent(function (GetSepaMandate $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/sepa_mandates/12345' === $sent->resolveEndpoint();
        });
    }
}
