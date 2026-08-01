<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetCustomerInvoiceCategories extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $customerInvoiceId,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->customerInvoiceId}/categories";
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
