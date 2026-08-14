<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\GocardlessMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\GetGocardlessMandate;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class GetGocardlessMandateTest extends TestCase
{
    public function testSendsAGetRequestToTheGocardlessMandateEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetGocardlessMandate(42));

        $mockClient->assertSent(function (GetGocardlessMandate $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/gocardless_mandates/42' === $sent->resolveEndpoint();
        });
    }
}
