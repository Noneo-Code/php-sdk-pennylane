<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\FileAttachment\FileAttachmentDto;
use Noneo\Sdk\Pennylane\Requests\FileAttachments\PostFileAttachments;
use Saloon\Http\BaseResource;

final class FileAttachments extends BaseResource
{
    /** @param resource|string $fileContents */
    public function upload(mixed $fileContents, ?string $filename = null): FileAttachmentDto
    {
        $request = new PostFileAttachments($fileContents, $filename);

        return FileAttachmentDto::fromArray($this->connector->send($request)->json());
    }
}
