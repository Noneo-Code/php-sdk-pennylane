<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Exports;

use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostAnalyticalGeneralLedgerExport extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $periodStart,
        private readonly string $periodEnd,
        private readonly ?AnalyticalGeneralLedgerExportMode $mode = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/exports/analytical_general_ledgers';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'mode' => $this->mode?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
