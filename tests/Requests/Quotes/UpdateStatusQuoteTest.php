<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Quotes;

use Noneo\Sdk\Pennylane\Dto\Quote\QuoteStatus;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Quotes\UpdateStatusQuote;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateStatusQuoteTest extends TestCase
{
    public function testSendsAPutRequestWithTheStatus(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateStatusQuote(42, QuoteStatus::Accepted));

        $mockClient->assertSent(function (UpdateStatusQuote $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/quotes/42/update_status' === $sent->resolveEndpoint()
                && ['status' => 'accepted'] === $sent->body()->all();
        });
    }
}
