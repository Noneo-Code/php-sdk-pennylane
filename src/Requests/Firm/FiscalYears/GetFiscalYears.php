<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\FiscalYears;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

/**
 * Contrairement à la Company API, la Firm API pagine cet endpoint par page
 * (`page`, `per_page`) plutôt que par curseur.
 */
final class GetFiscalYears extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?int $page = null,
        private readonly ?int $perPage = null,
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
            'page' => $this->page,
            'per_page' => $this->perPage,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
