<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Changelogs;

final class GetDmsFilesChanges extends ChangelogRequest
{
    public function resolveEndpoint(): string
    {
        return '/changelogs/dms_files';
    }
}
