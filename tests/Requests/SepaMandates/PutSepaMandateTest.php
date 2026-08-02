<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\PutSepaMandate;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutSepaMandateTest extends TestCase
{
    public function testSendsAPutRequestWithTheUpdatedFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSepaMandate(
            id: 12345,
            iban: 'FR3612739000506556687647Z31',
            sequenceType: SepaMandateSequenceType::Rcur,
        ));

        $mockClient->assertSent(function (PutSepaMandate $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/sepa_mandates/12345' === $sent->resolveEndpoint()
                && [
                    'iban' => 'FR3612739000506556687647Z31',
                    'sequence_type' => 'RCUR',
                ] === $sent->body()->all();
        });
    }

    public function testSendsAnEmptyBodyWhenNoFieldIsProvided(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSepaMandate(id: 12345));

        $mockClient->assertSent(function (PutSepaMandate $sent): bool {
            return [] === $sent->body()->all();
        });
    }
}
