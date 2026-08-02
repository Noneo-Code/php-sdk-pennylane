<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\PostSepaMandates;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostSepaMandatesTest extends TestCase
{
    public function testSendsAPostRequestWithTheMandateBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostSepaMandates(
            bic: 'BNPAFRPP',
            iban: 'FR3612739000506556687647Z31',
            signedAt: '2025-04-24',
            identifier: 'K-02-2025-12345',
            customerId: 47334785,
            bank: 'BNP Paribas',
            sequenceType: SepaMandateSequenceType::Frst,
        ));

        $mockClient->assertSent(function (PostSepaMandates $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/sepa_mandates' === $sent->resolveEndpoint()
                && [
                    'bank' => 'BNP Paribas',
                    'bic' => 'BNPAFRPP',
                    'iban' => 'FR3612739000506556687647Z31',
                    'sequence_type' => 'FRST',
                    'signed_at' => '2025-04-24',
                    'identifier' => 'K-02-2025-12345',
                    'customer_id' => 47334785,
                ] === $sent->body()->all();
        });
    }

    public function testOmitsOptionalFieldsWhenNotProvided(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostSepaMandates(
            bic: 'BNPAFRPP',
            iban: 'FR3612739000506556687647Z31',
            signedAt: '2025-04-24',
            identifier: 'K-02-2025-12345',
            customerId: 47334785,
        ));

        $mockClient->assertSent(function (PostSepaMandates $sent): bool {
            return [
                'bic' => 'BNPAFRPP',
                'iban' => 'FR3612739000506556687647Z31',
                'signed_at' => '2025-04-24',
                'identifier' => 'K-02-2025-12345',
                'customer_id' => 47334785,
            ] === $sent->body()->all();
        });
    }
}
