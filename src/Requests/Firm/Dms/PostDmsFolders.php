<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Dms;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostDmsFolders extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $name,
        private readonly ?int $parentFolderId = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/dms/folders';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'name' => $this->name,
            'parent_folder_id' => $this->parentFolderId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
