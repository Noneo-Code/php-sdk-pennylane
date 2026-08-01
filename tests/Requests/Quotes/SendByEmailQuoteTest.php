<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\SendByEmailQuote;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class SendByEmailQuoteTest extends TestCase
{
    public function testSendsAPostRequestToTheSendByEmailEndpointWithoutRecipients(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new SendByEmailQuote(42));

        $mockClient->assertSent(function (SendByEmailQuote $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/quotes/42/send_by_email' === $sent->resolveEndpoint()
                && [] === $sent->body()->all();
        });
    }

    public function testSendsTheRecipientsInTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new SendByEmailQuote(42, ['jane@example.com', 'john@example.com']));

        $mockClient->assertSent(function (SendByEmailQuote $sent): bool {
            return ['recipients' => ['jane@example.com', 'john@example.com']] === $sent->body()->all();
        });
    }
}
