<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Exports;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetSoftwareSpecificEntriesExport extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/exports/software_specific_entries/{$this->id}";
    }
}
