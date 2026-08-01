<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerAccounts;

use Noneo\Sdk\Pennylane\Dto\LedgerAccount\CountryAlpha2;
use Noneo\Sdk\Pennylane\Dto\LedgerAccount\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\PostLedgerAccounts;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostLedgerAccountsTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 124], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostLedgerAccounts(number: '512', label: 'Secondary Account'));

        $mockClient->assertSent(function (PostLedgerAccounts $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/ledger_accounts' === $sent->resolveEndpoint()
                && [
                    'number' => '512',
                    'label' => 'Secondary Account',
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalVatRateAndCountryAlpha2(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 124], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostLedgerAccounts(
            number: '512',
            label: 'Secondary Account',
            vatRate: VatRate::FR_200,
            countryAlpha2: CountryAlpha2::FR,
        ));

        $mockClient->assertSent(function (PostLedgerAccounts $sent): bool {
            return [
                'number' => '512',
                'label' => 'Secondary Account',
                'vat_rate' => 'FR_200',
                'country_alpha2' => 'FR',
            ] === $sent->body()->all();
        });
    }
}
