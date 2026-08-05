<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

final class NonStaticPayload
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getProduct')]
    public function documentedPayload(): array
    {
        return [];
    }
}
