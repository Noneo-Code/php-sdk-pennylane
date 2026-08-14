<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\LedgerAccountDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\PostLedgerAccounts;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\UpdateLedgerAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class LedgerAccountsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 124,
            'number' => '512',
            'label' => 'Secondary Account',
            'vat_rate' => 'any',
            'country_alpha2' => 'FR',
            'enabled' => true,
            'letterable' => false,
            'archived_at' => null,
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->ledgerAccounts()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_accounts' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(LedgerAccountDto::class, $page->items[0]);
        self::assertSame(124, $page->items[0]->id);
        self::assertNull($page->items[0]->type);
    }

    public function testGetSendsAGetRequestToTheLedgerAccountEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->ledgerAccounts()->get(124);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_accounts/124' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testCreateSendsAPostRequestWithTheLedgerAccountBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->ledgerAccounts()->create('512', 'Secondary Account');

        $mockClient->assertSent(function (PostLedgerAccounts $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_accounts' === $response->getPendingRequest()->getUrl()
                && ['number' => '512', 'label' => 'Secondary Account'] === $sent->body()->all();
        });
    }

    public function testUpdateSendsAPutRequestToTheLedgerAccountEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->ledgerAccounts()->update(124, letterable: true);

        $mockClient->assertSent(function (UpdateLedgerAccount $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_accounts/124' === $response->getPendingRequest()->getUrl()
                && ['letterable' => true] === $sent->body()->all();
        });
    }
}
