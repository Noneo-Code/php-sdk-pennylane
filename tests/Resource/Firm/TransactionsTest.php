<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Transaction\TransactionDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Transactions\CreateTransaction;
use Noneo\Sdk\Pennylane\Requests\Transactions\UpdateTransaction;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class TransactionsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'VIR SEPA',
            'attachment_required' => false,
            'date' => '2023-08-30',
            'outstanding_balance' => '49.3',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'archived_at' => null,
            'currency' => 'EUR',
            'currency_amount' => '120.00',
            'amount' => '120.00',
            'currency_fee' => null,
            'fee' => null,
            'journal' => ['id' => 7, 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals/7'],
            'bank_account' => ['id' => 53, 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/bank_accounts/53'],
            'pro_account_expense' => null,
            'customer' => null,
            'supplier' => null,
            'categories' => [],
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->transactions()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/transactions' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(TransactionDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
        self::assertNull($page->items[0]->matchedInvoicesUrl);
    }

    public function testCreateSendsAPostRequestWithTheTransactionBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->transactions()->create(53, 'VIR SEPA', '2023-08-30', '120.00');

        $mockClient->assertSent(function (CreateTransaction $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/transactions' === $response->getPendingRequest()->getUrl()
                && 53 === ($sent->body()->all()['bank_account_id'] ?? null);
        });
    }

    public function testUpdateSendsAPutRequestToTheTransactionEndpoint(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->transactions()->update(42, customerId: 7);

        $mockClient->assertSent(function (UpdateTransaction $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/transactions/42' === $response->getPendingRequest()->getUrl()
                && ['customer_id' => 7] === $sent->body()->all();
        });
    }
}
