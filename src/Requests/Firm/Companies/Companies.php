<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Companies;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class Companies extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?int $page = null,
        private readonly ?int $perPage = null,
        private readonly ?FilterSet $filters = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/companies';
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'per_page' => $this->perPage,
            'filter' => $this->filters instanceof FilterSet && !$this->filters->isEmpty() ? $this->filters->toJson() : null,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
