<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\ProAccountMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\PostProAccountMandateMailRequests;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostProAccountMandateMailRequestsTest extends TestCase
{
    public function testSendsAPostRequestWithTheCustomerId(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostProAccountMandateMailRequests(42));

        $mockClient->assertSent(function (PostProAccountMandateMailRequests $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/pro_account/mandate_requests' === $sent->resolveEndpoint()
                && ['customer_id' => 42] === $sent->body()->all();
        });
    }
}
