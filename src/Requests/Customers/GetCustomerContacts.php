<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Customers;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCustomerContacts extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $customerId,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
        private readonly ?string $sort = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customers/{$this->customerId}/contacts";
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'cursor' => $this->cursor,
            'limit' => $this->limit,
            'sort' => $this->sort,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
