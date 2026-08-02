<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\BillingSubscriptions;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetBillingSubscriptionInvoiceLineSections extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $billingSubscriptionId,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
        private readonly ?Sort $sort = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/billing_subscriptions/{$this->billingSubscriptionId}/invoice_line_sections";
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'cursor' => $this->cursor,
            'limit' => $this->limit,
            'sort' => $this->sort instanceof Sort ? (string) $this->sort : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
