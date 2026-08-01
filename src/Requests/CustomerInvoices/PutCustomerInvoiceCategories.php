<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutCustomerInvoiceCategories extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param list<CategoryAllocation> $allocations */
    public function __construct(
        private readonly int $customerInvoiceId,
        private readonly array $allocations,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->customerInvoiceId}/categories";
    }

    /** @return list<array{id: int, weight: string}> */
    protected function defaultBody(): array
    {
        return array_map(
            static fn (CategoryAllocation $allocation): array => $allocation->toArray(),
            $this->allocations,
        );
    }
}
