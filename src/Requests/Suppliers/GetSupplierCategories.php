<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Suppliers;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetSupplierCategories extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $supplierId,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/suppliers/{$this->supplierId}/categories";
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'cursor' => $this->cursor,
            'limit' => $this->limit,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
