<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\ProAccountMandates;

use Noneo\Sdk\Pennylane\Dto\Mandate\MandateType;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\PostProAccountMandateMigrations;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostProAccountMandateMigrationsTest extends TestCase
{
    public function testSendsAPostRequestWithTheMigrationBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['mandate_migration' => []], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostProAccountMandateMigrations(
            mandateType: MandateType::Mandate,
            mandateId: 12345,
            earlyExecutionDatePermitted: true,
        ));

        $mockClient->assertSent(function (PostProAccountMandateMigrations $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/pro_account/mandate_migrations' === $sent->resolveEndpoint()
                && [
                    'mandate_type' => 'Mandate',
                    'mandate_id' => 12345,
                    'early_execution_date_permitted' => true,
                ] === $sent->body()->all();
        });
    }

    public function testOmitsEarlyExecutionDatePermittedWhenNotProvided(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['mandate_migration' => []], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostProAccountMandateMigrations(mandateType: MandateType::SepaMandate, mandateId: 12345));

        $mockClient->assertSent(function (PostProAccountMandateMigrations $sent): bool {
            return [
                'mandate_type' => 'SepaMandate',
                'mandate_id' => 12345,
            ] === $sent->body()->all();
        });
    }
}
