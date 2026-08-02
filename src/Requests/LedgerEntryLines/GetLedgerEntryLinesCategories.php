<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetLedgerEntryLinesCategories extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $ledgerEntryLineId,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
        private readonly ?Sort $sort = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/ledger_entry_lines/{$this->ledgerEntryLineId}/categories";
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
