<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Exports;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetGeneralLedgerExport extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/exports/general_ledgers/{$this->id}";
    }
}
