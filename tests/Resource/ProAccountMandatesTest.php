<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Mandate\MandateMigrationCandidateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateType;
use Noneo\Sdk\Pennylane\Dto\Mandate\ProAccountMandateDto;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ProAccountMandatesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedMandatePayload(): array
    {
        return [
            'status' => 'enabled',
            'early_execution_date_permitted' => false,
            'active_billing_subscription' => false,
            'signed_at' => '2024-01-15',
            'created_at' => '2024-01-15T10:30:00Z',
            'pdf_url' => 'https://example.com/mandate.pdf',
            'customer' => ['id' => 47334785, 'url' => 'https://api.example.com/api/external/v2/customers/47334785'],
        ];
    }

    /** @return array<string, mixed> */
    private function documentedMigrationCandidatePayload(): array
    {
        return [
            'id' => 42,
            'status' => 'available',
            'direct_debit_method' => 'GoCardless',
            'signed_at' => '2023-08-07T14:23:12.000Z',
            'error_message' => null,
            'migrated_at' => null,
            'migration_started_at' => null,
            'mandate' => ['id' => 42, 'type' => 'Mandate'],
            'customer' => [
                'id' => 42,
                'url' => 'https://app.pennylane.com/api/external/v2/customers/42',
                'pro_account_mandate' => ['url' => 'https://app.pennylane.com/api/external/v2/pro_account/mandates?filter=...'],
            ],
            'created_at' => '2023-08-07T14:23:12.000Z',
            'updated_at' => '2023-08-07T14:23:12.000Z',
        ];
    }

    public function testListReturnsACursorPageOfProAccountMandateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedMandatePayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->proAccountMandates()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(ProAccountMandateDto::class, $page->items[0]);
        self::assertSame('enabled', $page->items[0]->status);
    }

    public function testMigrationCandidatesReturnsACursorPageOfMandateMigrationCandidateDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedMigrationCandidatePayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->proAccountMandates()->migrationCandidates();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(MandateMigrationCandidateDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testMigrateReturnsAMandateMigrationDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'mandate_migration' => [
                'id' => 42,
                'status' => 'pending',
                'direct_debit_method' => 'GoCardless',
                'signed_at' => '2023-08-07T14:23:12.000Z',
                'error_message' => null,
                'migrated_at' => null,
                'migration_started_at' => null,
                'created_at' => '2023-08-07T14:23:12.000Z',
                'updated_at' => '2023-08-07T14:23:12.000Z',
                'mandate' => ['id' => 42, 'type' => 'SepaMandate'],
                'customer' => [
                    'id' => 42,
                    'url' => 'https://app.pennylane.com/api/external/v2/customers/42',
                    'pro_account_mandate' => ['url' => 'https://app.pennylane.com/api/external/v2/pro_account/mandates?filter=...'],
                ],
            ],
        ], 201)]);
        $pennylane->withMockClient($mockClient);

        $migration = $pennylane->proAccountMandates()->migrate(MandateType::SepaMandate, 42);

        self::assertSame(42, $migration->id);
    }

    public function testSendMandateRequestSendsTheRequest(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->proAccountMandates()->sendMandateRequest(42);

        $mockClient->assertSentCount(1);
    }
}
