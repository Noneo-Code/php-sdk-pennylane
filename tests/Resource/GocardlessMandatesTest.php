<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Mandate\GocardlessMandateDto;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class GocardlessMandatesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'external_reference' => 'MD000VDD0HN1RP',
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'status' => 'active',
            'external_customer_account' => 'test_account_id',
            'external_customer_label' => 'test_label',
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testListReturnsACursorPageOfGocardlessMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->gocardlessMandates()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(GocardlessMandateDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testGetReturnsAGocardlessMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $mandate = $pennylane->gocardlessMandates()->get(42);

        self::assertSame(42, $mandate->id);
    }

    public function testSendMailRequestSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->gocardlessMandates()->sendMailRequest(customerId: 42, recipients: ['customer@example.com']);

        $mockClient->assertSentCount(1);
    }

    public function testAssociateSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 200)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->gocardlessMandates()->associate(42, 1234);

        $mockClient->assertSentCount(1);
    }

    public function testCancelSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->gocardlessMandates()->cancel(42);

        $mockClient->assertSentCount(1);
    }
}
