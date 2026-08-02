<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class SepaMandatesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 12345,
            'bank' => 'BNP Paribas',
            'bic' => 'BNPAFRPP',
            'iban' => 'FR3612739000506556687647Z31',
            'sequence_type' => 'RCUR',
            'signed_at' => '2025-04-24',
            'identifier' => 'K-02-2023-12345',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testListReturnsACursorPageOfSepaMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->sepaMandates()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(SepaMandateDto::class, $page->items[0]);
        self::assertSame(12345, $page->items[0]->id);
    }

    public function testCreateReturnsASepaMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $mandate = $pennylane->sepaMandates()->create(
            bic: 'BNPAFRPP',
            iban: 'FR3612739000506556687647Z31',
            signedAt: '2025-04-24',
            identifier: 'K-02-2023-12345',
            customerId: 42,
        );

        self::assertSame(12345, $mandate->id);
    }

    public function testGetReturnsASepaMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $mandate = $pennylane->sepaMandates()->get(12345);

        self::assertSame(12345, $mandate->id);
    }

    public function testUpdateReturnsASepaMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $mandate = $pennylane->sepaMandates()->update(12345, sequenceType: SepaMandateSequenceType::Rcur);

        self::assertSame(12345, $mandate->id);
    }

    public function testDeleteSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->sepaMandates()->delete(12345);

        $mockClient->assertSentCount(1);
    }
}
