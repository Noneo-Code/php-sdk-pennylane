<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Categories;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateCategory extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly string|Clear|null $label = null,
        private readonly CategoryDirection|Clear|null $direction = null,
        private readonly string|Clear|null $analyticalCode = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/categories/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'label' => $this->label,
            'direction' => $this->direction,
            'analytical_code' => $this->analyticalCode,
        ]);
    }
}
