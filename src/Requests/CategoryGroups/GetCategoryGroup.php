<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CategoryGroups;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetCategoryGroup extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/category_groups/{$this->id}";
    }
}
