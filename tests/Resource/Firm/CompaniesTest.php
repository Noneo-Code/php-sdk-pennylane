<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Company\CompanyDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class CompaniesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedCompanyPayload(): array
    {
        return [
            'id' => 22938,
            'name' => 'Koss Inc.',
            'billing_company_name' => 'Koss',
            'siren' => '362521879',
            'address' => '35508 Mary Plain',
            'city' => 'Jefferson City',
            'postal_code' => '77379',
            'activity_nomenclature' => 'NAFRev2',
            'activity_code' => '1234A',
            'external_id' => 'G6YYYLPXIZ',
            'client_code' => 'DOSSIER_ABC',
        ];
    }

    public function testListSendsAGetRequestAndMapsThePageEnvelope(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [$this->documentedCompanyPayload()],
            'total_pages' => 3,
            'current_page' => 1,
            'total_items' => 5,
            'per_page' => 2,
        ])]);
        $firm->withMockClient($mockClient);

        $page = $firm->companies()->list(page: 1, perPage: 2);

        $mockClient->assertSent(function (Request $sent): bool {
            return '/companies' === $sent->resolveEndpoint();
        });

        self::assertCount(1, $page->items);
        self::assertInstanceOf(CompanyDto::class, $page->items[0]);
        self::assertSame(22938, $page->items[0]->id);
        self::assertSame(3, $page->totalPages);
        self::assertSame(1, $page->currentPage);
        self::assertSame(5, $page->totalItems);
        self::assertSame(2, $page->perPage);
    }

    public function testPaginateTraversesEveryPage(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCompanyPayload()],
                'total_pages' => 2,
                'current_page' => 1,
                'total_items' => 2,
                'per_page' => 1,
            ]),
            MockResponse::make([
                'items' => [array_merge($this->documentedCompanyPayload(), ['id' => 1])],
                'total_pages' => 2,
                'current_page' => 2,
                'total_items' => 2,
                'per_page' => 1,
            ]),
        ]);
        $firm->withMockClient($mockClient);

        $companies = iterator_to_array($firm->companies()->paginate());

        self::assertCount(2, $companies);
        self::assertSame(22938, $companies[0]->id);
        self::assertSame(1, $companies[1]->id);
    }

    public function testGetSendsAGetRequestToTheCompanyEndpoint(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedCompanyPayload())]);
        $firm->withMockClient($mockClient);

        $company = $firm->companies()->get(22938);

        $mockClient->assertSent(function (Request $sent): bool {
            return '/companies/22938' === $sent->resolveEndpoint();
        });

        self::assertSame(22938, $company->id);
        self::assertSame('Koss Inc.', $company->name);
    }
}
