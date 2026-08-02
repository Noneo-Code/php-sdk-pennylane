<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutTransactionCategories extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param list<CategoryAllocation> $allocations */
    public function __construct(
        private readonly int $transactionId,
        private readonly array $allocations,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/transactions/{$this->transactionId}/categories";
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
