<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Categories;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostCategories extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $label,
        private readonly int $categoryGroupId,
        private readonly ?CategoryDirection $direction = null,
        private readonly ?string $analyticalCode = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/categories';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'label' => $this->label,
            'category_group_id' => $this->categoryGroupId,
            'direction' => $this->direction?->value,
            'analytical_code' => $this->analyticalCode,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
