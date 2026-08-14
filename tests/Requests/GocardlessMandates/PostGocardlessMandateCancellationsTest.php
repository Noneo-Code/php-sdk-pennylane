<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\GocardlessMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\PostGocardlessMandateCancellations;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostGocardlessMandateCancellationsTest extends TestCase
{
    public function testSendsAPostRequestToTheCancellationsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostGocardlessMandateCancellations(42));

        $mockClient->assertSent(function (PostGocardlessMandateCancellations $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/gocardless_mandates/42/cancellations' === $sent->resolveEndpoint();
        });
    }
}
