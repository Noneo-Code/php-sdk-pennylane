<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Dms;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

final class PostDmsFiles extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /** @param resource|string $fileContents */
    public function __construct(
        private readonly mixed $fileContents,
        private readonly ?string $name = null,
        private readonly ?int $parentFolderId = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/dms/files';
    }

    /** @return array<MultipartValue> */
    protected function defaultBody(): array
    {
        $parts = [new MultipartValue('file', $this->fileContents, $this->name)];

        if (null !== $this->name) {
            $parts[] = new MultipartValue('name', $this->name);
        }

        if (null !== $this->parentFolderId) {
            $parts[] = new MultipartValue('parent_folder_id', (string) $this->parentFolderId);
        }

        return $parts;
    }
}
