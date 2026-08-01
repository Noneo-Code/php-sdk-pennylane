<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\FiscalYears;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetFiscalYears extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
        private readonly ?Sort $sort = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/fiscal_years';
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
