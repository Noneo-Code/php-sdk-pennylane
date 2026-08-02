<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\TrialBalance;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetTrialBalance extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $periodStart,
        private readonly string $periodEnd,
        private readonly ?bool $isAuxiliary = null,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/trial_balance';
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'is_auxiliary' => $this->isAuxiliary,
            'cursor' => $this->cursor,
            'limit' => $this->limit,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
