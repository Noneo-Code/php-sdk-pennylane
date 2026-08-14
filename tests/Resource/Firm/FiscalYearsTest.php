<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\FiscalYear\FiscalYearDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\Firm\FiscalYears\PostFiscalYears;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class FiscalYearsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return ['id' => 12, 'start' => '2023-01-01', 'finish' => '2023-12-31', 'status' => 'open'];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make([
            'items' => [$this->documentedPayload()],
            'total_pages' => 1,
            'current_page' => 1,
            'total_items' => 1,
            'per_page' => 20,
        ])]);
        $firm->connector()->withMockClient($mockClient);

        $page = $firm->company(1234)->fiscalYears()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/fiscal_years' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(FiscalYearDto::class, $page->items[0]);
        self::assertSame(12, $page->items[0]->id);
        self::assertNull($page->items[0]->createdAt);
    }

    public function testCreateSendsAPostRequestWithTheFiscalYearBody(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->connector()->withMockClient($mockClient);

        $firm->company(1234)->fiscalYears()->create('2023-01-01', '2023-12-31');

        $mockClient->assertSent(function (PostFiscalYears $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/fiscal_years' === $response->getPendingRequest()->getUrl()
                && ['start' => '2023-01-01', 'finish' => '2023-12-31'] === $sent->body()->all();
        });
    }
}
