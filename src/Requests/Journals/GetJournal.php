<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Journals;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetJournal extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/journals/{$this->id}";
    }
}
