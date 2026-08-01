<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Quotes;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

final class GetQuoteAppendices extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $quoteId,
        private readonly ?string $cursor = null,
        private readonly ?int $limit = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/quotes/{$this->quoteId}/appendices";
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'cursor' => $this->cursor,
            'limit' => $this->limit,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
