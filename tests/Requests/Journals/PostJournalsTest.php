<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Journals;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Journals\PostJournals;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostJournalsTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostJournals(code: 'RB', label: 'Journal de Reprise Balance'));

        $mockClient->assertSent(function (PostJournals $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/journals' === $sent->resolveEndpoint()
                && [
                    'code' => 'RB',
                    'label' => 'Journal de Reprise Balance',
                ] === $sent->body()->all();
        });
    }
}
