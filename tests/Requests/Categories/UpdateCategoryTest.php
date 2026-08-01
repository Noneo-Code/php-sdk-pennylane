<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Categories;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Categories\UpdateCategory;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateCategoryTest extends TestCase
{
    public function testSendsAPutRequestWithNoFieldsWhenNothingChanges(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateCategory(42));

        $mockClient->assertSent(function (UpdateCategory $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/categories/42' === $sent->resolveEndpoint()
                && [] === $sent->body()->all();
        });
    }

    public function testSendsOnlyTheProvidedFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateCategory(
            42,
            label: 'Alimentaire',
            direction: CategoryDirection::CashIn,
            analyticalCode: 'CODE123',
        ));

        $mockClient->assertSent(function (UpdateCategory $sent): bool {
            return [
                'label' => 'Alimentaire',
                'direction' => 'cash_in',
                'analytical_code' => 'CODE123',
            ] === $sent->body()->all();
        });
    }
}
