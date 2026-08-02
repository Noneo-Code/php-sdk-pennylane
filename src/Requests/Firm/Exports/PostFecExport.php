<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Exports;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostFecExport extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $periodStart,
        private readonly string $periodEnd,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/exports/fecs';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return [
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
        ];
    }
}
