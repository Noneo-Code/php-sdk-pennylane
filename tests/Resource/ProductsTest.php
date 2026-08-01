<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Product\ProductDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Products\PostProducts;
use Noneo\Sdk\Pennylane\Requests\Products\PutProduct;
use Noneo\Sdk\Pennylane\Tests\Dto\Product\ProductDtoTest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ProductsTest extends TestCase
{
    public function testListReturnsACursorPageOfProductDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [ProductDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $page = $pennylane->products()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(ProductDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testPaginateYieldsProductDtoAcrossPages(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([
            MockResponse::make(['items' => [ProductDtoTest::documentedPayload()], 'has_more' => false, 'next_cursor' => null]),
        ]));

        $products = iterator_to_array($pennylane->products()->paginate());

        self::assertCount(1, $products);
        self::assertInstanceOf(ProductDto::class, $products[0]);
    }

    public function testGetReturnsAProductDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $pennylane->withMockClient(new MockClient([MockResponse::make(ProductDtoTest::documentedPayload())]));

        $product = $pennylane->products()->get(1);

        self::assertInstanceOf(ProductDto::class, $product);
        self::assertSame(1, $product->id);
    }

    public function testCreateReturnsAProductDtoAndSendsTheBody(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(ProductDtoTest::documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $product = $pennylane->products()->create(
            label: 'Product 1',
            priceBeforeTax: '100.00',
            vatRate: \Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate::Fr200,
        );

        self::assertInstanceOf(ProductDto::class, $product);
        $mockClient->assertSent(PostProducts::class);
    }

    public function testUpdateReturnsAProductDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(ProductDtoTest::documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $product = $pennylane->products()->update(1, label: 'Renamed product');

        self::assertInstanceOf(ProductDto::class, $product);
        $mockClient->assertSent(PutProduct::class);
    }
}
