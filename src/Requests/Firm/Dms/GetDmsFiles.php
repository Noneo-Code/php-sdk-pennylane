<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Dms;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetDmsFiles extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
        private readonly ?FilterSet $filters = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/dms/files';
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'cursor' => $this->cursor,
            'limit' => $this->limit,
            'filter' => $this->filters instanceof FilterSet && !$this->filters->isEmpty() ? $this->filters->toJson() : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
