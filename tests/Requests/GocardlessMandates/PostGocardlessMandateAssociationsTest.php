<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\GocardlessMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\PostGocardlessMandateAssociations;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostGocardlessMandateAssociationsTest extends TestCase
{
    public function testSendsAPostRequestWithTheCustomerId(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 200)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostGocardlessMandateAssociations(gocardlessMandateId: 42, customerId: 1234));

        $mockClient->assertSent(function (PostGocardlessMandateAssociations $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/gocardless_mandates/42/associations' === $sent->resolveEndpoint()
                && ['customer_id' => 1234] === $sent->body()->all();
        });
    }
}
