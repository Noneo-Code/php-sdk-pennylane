<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategoryAllocation;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutLedgerEntryLinesCategories extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param list<CategoryAllocation> $allocations */
    public function __construct(
        private readonly int $ledgerEntryLineId,
        private readonly array $allocations,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/ledger_entry_lines/{$this->ledgerEntryLineId}/categories";
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
