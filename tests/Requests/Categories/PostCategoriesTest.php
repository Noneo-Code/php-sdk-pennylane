<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Categories;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Categories\PostCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostCategoriesTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostCategories(label: 'Loyers', categoryGroupId: 23));

        $mockClient->assertSent(function (PostCategories $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/categories' === $sent->resolveEndpoint()
                && [
                    'label' => 'Loyers',
                    'category_group_id' => 23,
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalDirectionAndAnalyticalCode(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostCategories(
            label: 'Loyers',
            categoryGroupId: 23,
            direction: CategoryDirection::CashOut,
            analyticalCode: 'CODE123',
        ));

        $mockClient->assertSent(function (PostCategories $sent): bool {
            return [
                'label' => 'Loyers',
                'category_group_id' => 23,
                'direction' => 'cash_out',
                'analytical_code' => 'CODE123',
            ] === $sent->body()->all();
        });
    }
}
