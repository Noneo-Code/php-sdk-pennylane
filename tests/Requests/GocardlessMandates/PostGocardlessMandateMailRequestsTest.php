<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\GocardlessMandates;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\PostGocardlessMandateMailRequests;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostGocardlessMandateMailRequestsTest extends TestCase
{
    public function testSendsAPostRequestWithTheEmailBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostGocardlessMandateMailRequests(
            customerId: 42,
            recipients: ['customer@example.com'],
            subject: 'Please authorize your direct debit mandate',
            emailBody: 'Please click the link to authorize your direct debit mandate.',
        ));

        $mockClient->assertSent(function (PostGocardlessMandateMailRequests $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/gocardless_mandates/mail_requests' === $sent->resolveEndpoint()
                && [
                    'customer_id' => 42,
                    'email' => [
                        'subject' => 'Please authorize your direct debit mandate',
                        'body' => 'Please click the link to authorize your direct debit mandate.',
                        'recipients' => ['customer@example.com'],
                    ],
                ] === $sent->body()->all();
        });
    }

    public function testOmitsOptionalSubjectAndBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostGocardlessMandateMailRequests(customerId: 42, recipients: ['customer@example.com']));

        $mockClient->assertSent(function (PostGocardlessMandateMailRequests $sent): bool {
            return [
                'customer_id' => 42,
                'email' => ['recipients' => ['customer@example.com']],
            ] === $sent->body()->all();
        });
    }
}
